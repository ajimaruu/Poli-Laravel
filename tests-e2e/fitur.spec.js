// =====================================================================
// PENGUJIAN SEMUA FITUR FAST TRACK - urut sesuai alur nyata aplikasi:
//   pasien daftar -> dokter periksa (live update) -> riwayat -> bayar
//   -> admin verifikasi -> notifikasi -> export -> ganti password
// Nama setiap test diawali [Poin N] sesuai poin penilaian di soal.
// =====================================================================
import { test } from '@playwright/test';
import ExcelJS from 'exceljs';
import { AKUN, login, pesanFlash, tokenCsrf, jumlahLonceng, bukaLonceng, GAMBAR_PNG, expect } from './helpers.js';

// # serial: test berikutnya hanya jalan jika test sebelumnya lolos,
// # karena datanya berlanjut (misalnya: tagihan baru ada setelah dokter memeriksa).
test.describe.configure({ mode: 'serial' });

let rina, dimas, dokter, admin;       // 4 user login bersamaan
let urlDetailRina;                    // dipakai untuk uji akses milik orang lain
const PASSWORD_BARU = 'rahasiaBaru123';

let halamanTerakhir;                  // halaman yang terakhir melakukan request

test.beforeAll(async ({ browser }) => {
    rina = await login(browser, AKUN.rina);
    dimas = await login(browser, AKUN.dimas);
    dokter = await login(browser, AKUN.dokter);
    admin = await login(browser, AKUN.admin);

    // # Catat halaman mana yang terakhir aktif, supaya foto di laporan sesuai test-nya
    for (const p of [rina, dimas, dokter, admin]) p.on('request', () => { halamanTerakhir = p; });
});

// # Setiap test melampirkan foto layar ke laporan HTML (npm run test:e2e:report)
test.afterEach(async ({}, testInfo) => {
    if (halamanTerakhir && !halamanTerakhir.isClosed()) {
        await testInfo.attach('layar', { body: await halamanTerakhir.screenshot(), contentType: 'image/png' });
    }
});

test.afterAll(async () => {
    for (const p of [rina, dimas, dokter, admin]) await p?.context().close();
});

async function daftarPoli(page, namaPoli, keluhan) {
    await page.goto('/pasien/daftar');
    await page.selectOption('#id_poli', { label: namaPoli });
    await page.selectOption('#id_jadwal', { index: 1 });
    await page.fill('textarea[name=keluhan]', keluhan);
    await page.click('button:has-text("Daftar Poli")');
    await page.waitForLoadState('networkidle');
}

// ---------------------------------------------------------------------
// POIN 1 - DASHBOARD PASIEN
// ---------------------------------------------------------------------
test('[Poin 1] Tanpa antrian aktif: banner tidak tampil, muncul pesan kosong', async () => {
    await rina.goto('/pasien/dashboard');
    await expect(rina.locator('#banner-antrian')).toHaveCount(0);
    await expect(rina.getByText('Anda belum memiliki antrian aktif')).toBeVisible();
});

test('[Poin 1] Tabel jadwal: no, poli, dokter, hari, jam periksa, nomor dilayani', async () => {
    const header = (await rina.locator('table thead th').allInnerTexts()).map((t) => t.trim().toUpperCase());
    expect(header).toEqual(['NO', 'POLI', 'DOKTER', 'HARI', 'JAM PERIKSA', 'SEDANG DILAYANI']);
    await expect(rina.locator('table tbody tr')).toHaveCount(3);
});

test('[Poin 1] Daftar poli -> banner antrian aktif lengkap (poli, dokter, jadwal, nomor saya, dilayani)', async () => {
    await daftarPoli(rina, 'Poli Umum', 'Demam 3 hari');
    expect(await pesanFlash(rina)).toContain('Nomor antrian Anda: 1');

    const banner = rina.locator('#banner-antrian');
    await expect(banner).toContainText('Poli Umum');
    await expect(banner).toContainText('dr. Budi Santoso');
    await expect(banner).toContainText('Senin, 08:00 - 12:00');
    await expect(banner).toContainText('Nomor Anda');
    await expect(banner.locator('[data-dilayani]')).toHaveText('1');
    await expect(banner).toContainText('Giliran Anda');
});

test('[Poin 1] Pasien kedua mendapat nomor 2 dan melihat "1 antrian lagi"', async () => {
    await daftarPoli(dimas, 'Poli Umum', 'Batuk pilek');
    expect(await pesanFlash(dimas)).toContain('Nomor antrian Anda: 2');
    await expect(dimas.locator('#pesan-status')).toContainText('1 antrian lagi');
});

test('[Poin 1] Tidak bisa daftar ke 2 poli sekaligus: form terkunci', async () => {
    await dimas.goto('/pasien/daftar');
    await expect(dimas.locator('fieldset[disabled]')).toHaveCount(1);
    await expect(dimas.getByText('Anda masih memiliki antrian aktif')).toBeVisible();
});

test('[Poin 1] Tidak bisa daftar ke 2 poli sekaligus: server menolak walau form dipaksa kirim', async () => {
    const token = await tokenCsrf(dimas);
    // # Kirim POST langsung ke Poli Gigi, melewati form yang terkunci
    await dimas.request.post('/pasien/daftar', { form: { _token: token, id_poli: '2', id_jadwal: '2', keluhan: 'paksa' } });
    await dimas.goto('/pasien/riwayat');
    await expect(dimas.locator('table tbody tr')).toHaveCount(1); // tetap hanya 1 pendaftaran
});

// ---------------------------------------------------------------------
// POIN 4 - SISTEM ANTRIAN REAL-TIME (Reverb + Echo)
// ---------------------------------------------------------------------
test('[Poin 4] Dashboard terhubung ke Reverb (indikator Live)', async () => {
    await dimas.goto('/pasien/dashboard');
    await expect(dimas.locator('#status-live')).toContainText('Live', { timeout: 10_000 });
    await expect(dimas.locator('#banner-antrian [data-dilayani]')).toHaveText('1');
});

test('[Poin 4] Dokter memeriksa pasien no.1 -> angka di layar pasien no.2 berubah TANPA reload', async () => {
    // # Halaman dimas TIDAK di-reload sejak test sebelumnya.
    // # Penanda: variabel di window hilang kalau halaman sempat dimuat ulang.
    await dimas.evaluate(() => { window.__belumReload = true; });

    await dokter.goto('/dokter/periksa-pasien');
    await dokter.locator('table a:has-text("Periksa")').first().click();
    await dokter.waitForLoadState('networkidle');

    // Obat stok 0 tidak bisa dipilih
    await expect(dokter.locator('#pilih_obat option', { hasText: 'Vitamin C' })).toBeDisabled();

    for (const nama of ['Paracetamol', 'Amoxicillin']) {
        const value = await dokter.locator('#pilih_obat option', { hasText: nama }).getAttribute('value');
        await dokter.selectOption('#pilih_obat', value);
    }
    await dokter.fill('textarea[name=catatan]', 'Istirahat cukup, minum obat 3x sehari.');
    await dokter.click('button:has-text("Simpan")');
    await dokter.waitForLoadState('networkidle');
    expect((await pesanFlash(dokter)).toLowerCase()).toContain('berhasil disimpan');

    // # Angka di layar dimas berubah 1 -> 2 dan pesannya jadi "Giliran Anda"
    await expect(dimas.locator('#banner-antrian [data-dilayani]')).toHaveText('2', { timeout: 10_000 });
    await expect(dimas.locator('table [data-dilayani="1"]')).toHaveText('2');
    await expect(dimas.locator('#pesan-status')).toContainText('Giliran Anda');
    expect(await dimas.evaluate(() => window.__belumReload)).toBe(true);

    halamanTerakhir = dimas; // foto di laporan = layar pasien no.2 yang berubah tanpa reload
});

test('[Poin 1] Setelah diperiksa, banner pasien no.1 hilang dan ia boleh daftar lagi', async () => {
    await rina.goto('/pasien/dashboard');
    await expect(rina.locator('#banner-antrian')).toHaveCount(0);
    await rina.goto('/pasien/daftar');
    await expect(rina.locator('fieldset[disabled]')).toHaveCount(0);
});

// ---------------------------------------------------------------------
// POIN 3 - RIWAYAT PENDAFTARAN POLI
// ---------------------------------------------------------------------
test('[Poin 3] Menu Riwayat Pendaftaran ada di sidebar pasien', async () => {
    await rina.goto('/pasien/dashboard');
    await expect(rina.locator('aside a', { hasText: 'Riwayat Pendaftaran' })).toBeVisible();
});

test('[Poin 3] Riwayat berisi poli, dokter, jadwal, nomor antrian, status', async () => {
    await rina.goto('/pasien/riwayat');
    const baris = rina.locator('table tbody tr').first();
    for (const teks of ['Poli Umum', 'dr. Budi Santoso', 'Senin', '1', 'Sudah Diperiksa']) {
        await expect(baris).toContainText(teks);
    }
});

test('[Poin 3] Detail: catatan dokter, daftar obat, total biaya (dihitung server)', async () => {
    await rina.locator('table a:has-text("Detail")').first().click();
    await rina.waitForLoadState('networkidle');
    urlDetailRina = rina.url();

    await expect(rina.getByText('Istirahat cukup, minum obat 3x sehari.')).toBeVisible();
    await expect(rina.locator('main, .main-scroll').first()).toContainText('Paracetamol');
    await expect(rina.locator('main, .main-scroll').first()).toContainText('Amoxicillin');
    // 5.000 + 12.000 + jasa dokter 150.000
    await expect(rina.locator('.main-scroll')).toContainText('Rp 167.000');
});

test('[Poin 3] Pasien lain tidak bisa membuka detail milik orang lain (404)', async () => {
    const res = await dimas.goto(urlDetailRina);
    expect(res.status()).toBe(404);
});

// ---------------------------------------------------------------------
// POIN 6 - UPLOAD BUKTI PEMBAYARAN
// ---------------------------------------------------------------------
test('[Poin 6] Menu Pembayaran pasien menampilkan tagihan hasil pemeriksaan', async () => {
    await rina.goto('/pasien/dashboard');
    await rina.locator('aside a', { hasText: 'Pembayaran' }).click();
    await expect(rina).toHaveURL(/\/pasien\/pembayaran/);
    await expect(rina.locator('table tbody tr').first()).toContainText('Rp 167.000');
    await expect(rina.locator('table tbody tr').first()).toContainText('Belum Bayar');
});

test('[Poin 6] Upload file bukan gambar ditolak validasi', async () => {
    await rina.setInputFiles('input[name=bukti_bayar]', {
        name: 'bukti.txt', mimeType: 'text/plain', buffer: Buffer.from('bukan gambar'),
    });
    await rina.click('button:has-text("Upload")');
    await rina.waitForLoadState('networkidle');
    await expect(rina.getByText('File harus berupa gambar.')).toBeVisible();
});

test('[Poin 6] Upload foto bukti -> status Menunggu Verifikasi', async () => {
    await rina.setInputFiles('input[name=bukti_bayar]', GAMBAR_PNG);
    await rina.click('button:has-text("Upload")');
    await rina.waitForLoadState('networkidle');
    expect(await pesanFlash(rina)).toContain('berhasil diunggah');
    await expect(rina.locator('table tbody tr').first()).toContainText('Menunggu Verifikasi');
});

test('[Poin 2] Upload kedua kali (klik ganda) ditolak, dokter tetap 1 notifikasi', async () => {
    const token = await tokenCsrf(rina);
    await rina.request.post('/pasien/pembayaran/1/upload', {
        multipart: { _token: token, bukti_bayar: GAMBAR_PNG },
    });
    await dokter.goto('/dokter/periksa-pasien');
    expect(await jumlahLonceng(dokter)).toBe(1);
});

test('[Poin 6] Admin melihat tagihan menunggu verifikasi beserta foto bukti', async () => {
    await admin.goto('/admin/pembayaran');
    const baris = admin.locator('table').first().locator('tbody tr').first();
    await expect(baris).toContainText('Rina Amelia');
    await expect(baris).toContainText('Rp 167.000');

    await baris.locator('button > img').click(); // klik thumbnail -> buka modal foto
    await expect(admin.locator('dialog[open] img')).toBeVisible();
    await admin.keyboard.press('Escape');
});

test('[Poin 6] Admin konfirmasi -> status Lunas di sisi admin dan pasien', async () => {
    admin.once('dialog', (d) => d.accept());     // confirm("Konfirmasi ... LUNAS?")
    await admin.click('button:has-text("Konfirmasi Lunas")');
    await admin.waitForLoadState('networkidle');
    expect(await pesanFlash(admin)).toContain('LUNAS');
    await expect(admin.getByText('Tidak ada pembayaran yang menunggu verifikasi')).toBeVisible();

    await rina.goto('/pasien/pembayaran');
    await expect(rina.locator('table tbody tr').first()).toContainText('Lunas');
});

// ---------------------------------------------------------------------
// POIN 2 - NOTIFIKASI
// ---------------------------------------------------------------------
test('[Poin 2] Konfirmasi kedua kali ditolak, pasien tetap 1 notifikasi', async () => {
    const token = await tokenCsrf(admin);
    await admin.request.post('/admin/pembayaran/1/konfirmasi', { form: { _token: token, _method: 'PATCH' } });
    await rina.goto('/pasien/dashboard');
    expect(await jumlahLonceng(rina)).toBe(1);
});

test('[Poin 2] Dokter: lonceng berisi notifikasi upload bukti (pesan + waktu)', async () => {
    await dokter.goto('/dokter/periksa-pasien');
    const dropdown = await bukaLonceng(dokter);
    await expect(dropdown).toContainText('Bukti pembayaran diunggah');
    await expect(dropdown).toContainText('Rina Amelia');
    await expect(dropdown).toContainText('yang lalu');
});

test('[Poin 2] Pasien: lonceng berisi notifikasi pembayaran lunas', async () => {
    const dropdown = await bukaLonceng(rina);
    await expect(dropdown).toContainText('Pembayaran lunas');
    await expect(dropdown).toContainText('telah diverifikasi admin');
});

test('[Poin 2] Notifikasi belum dibaca dibedakan secara visual', async () => {
    await rina.goto('/notifikasi');
    const item = rina.locator('.main-scroll .bg-indigo-50\\/70').first();
    await expect(item).toBeVisible();                         // latar biru = belum dibaca
    await expect(item.locator('button[title="Tandai sudah dibaca"]')).toBeVisible();
});

test('[Poin 2] Pengguna lain tidak bisa menandai notifikasi milik orang lain (404)', async () => {
    const action = await rina.locator('.main-scroll form[action$="/baca"]').first().getAttribute('action');
    const token = await tokenCsrf(dimas);
    const res = await dimas.request.post(action, { form: { _token: token }, maxRedirects: 0 });
    expect(res.status()).toBe(404);
});

test('[Poin 2] Pengguna tanpa notifikasi melihat pesan kosong', async () => {
    await dimas.goto('/pasien/dashboard');
    expect(await jumlahLonceng(dimas)).toBe(0);
    const dropdown = await bukaLonceng(dimas);
    await expect(dropdown).toContainText('Belum ada notifikasi');
});

test('[Poin 2] Tombol tandai dibaca -> badge hilang, tampilan berubah', async () => {
    await rina.goto('/notifikasi');
    await rina.locator('.main-scroll form[action$="/baca"] button').first().click();
    await rina.waitForLoadState('networkidle');
    expect(await jumlahLonceng(rina)).toBe(0);
    await expect(rina.locator('.main-scroll .bg-indigo-50\\/70')).toHaveCount(0);
});

test('[Poin 3] Riwayat diurutkan dari pendaftaran terbaru', async () => {
    await daftarPoli(rina, 'Poli Gigi', 'Sakit gigi');
    await rina.goto('/pasien/riwayat');
    const baris = rina.locator('table tbody tr');
    await expect(baris).toHaveCount(2);
    await expect(baris.nth(0)).toContainText('Poli Gigi');     // terbaru di atas
    await expect(baris.nth(0)).toContainText('Menunggu');
    await expect(baris.nth(1)).toContainText('Poli Umum');
});

// ---------------------------------------------------------------------
// POIN 5 - EXPORT EXCEL
// ---------------------------------------------------------------------
async function unduhExcel(page, urlHalaman) {
    await page.goto(urlHalaman);
    const [download] = await Promise.all([
        page.waitForEvent('download'),
        page.click('a:has-text("Export Excel")'),
    ]);
    // # Buka file .xlsx yang terunduh lalu baca isinya baris per baris
    const workbook = new ExcelJS.Workbook();
    await workbook.xlsx.readFile(await download.path());
    const sheet = workbook.worksheets[0];
    const baris = [];
    sheet.eachRow((row) => baris.push(row.values.slice(1).map((v) => (v === null || v === undefined ? '' : String(v)))));
    return { nama: download.suggestedFilename(), header: baris[0], data: baris.slice(1) };
}

test('[Poin 5] Admin: export Data Dokter', async () => {
    const x = await unduhExcel(admin, '/admin/dokter');
    expect(x.nama).toMatch(/^data-dokter-.*\.xlsx$/);
    expect(x.header).toEqual(['No', 'Nama Dokter', 'Email', 'No. KTP', 'No. HP', 'Alamat', 'Poli']);
    const budi = x.data.find((b) => b[1] === 'dr. Budi Santoso');
    expect(budi[3]).toMatch(/^\d{16}$/);                        // No. KTP utuh 16 digit
    expect(budi[6]).toBe('Poli Umum');
});

test('[Poin 5] Admin: export Data Pasien', async () => {
    const x = await unduhExcel(admin, '/admin/pasien');
    expect(x.header).toEqual(['No', 'No. RM', 'Nama Pasien', 'Email', 'No. KTP', 'No. HP', 'Alamat']);
    expect(x.data.map((b) => b[2])).toEqual(expect.arrayContaining(['Rina Amelia', 'Dimas Pratama']));
});

test('[Poin 5] Admin: export Data Obat termasuk kolom stok dan harga', async () => {
    const x = await unduhExcel(admin, '/admin/obat');
    expect(x.header).toEqual(['No', 'Nama Obat', 'Kemasan', 'Harga (Rp)', 'Stok']);
    const para = x.data.find((b) => b[1].startsWith('Paracetamol'));
    expect(para[3]).toBe('5000');
    expect(para[4]).toBe('99');                                 // stok 100 berkurang 1 saat diresepkan
});

test('[Poin 5] Dokter: export Jadwal Periksa hanya milik dokter yang login', async () => {
    const x = await unduhExcel(dokter, '/dokter/jadwal-periksa');
    expect(x.header).toEqual(['No', 'Hari', 'Jam Mulai', 'Jam Selesai', 'Status']);
    expect(x.data).toEqual([['1', 'Senin', '08:00', '12:00', 'Aktif']]);
});

test('[Poin 5] Dokter: export Riwayat Pasien yang pernah ia periksa', async () => {
    const x = await unduhExcel(dokter, '/dokter/riwayat-pasien');
    expect(x.header[0]).toBe('No');
    expect(x.data).toHaveLength(1);
    expect(x.data[0]).toEqual(expect.arrayContaining(['Rina Amelia', 'Demam 3 hari', '167000', 'Lunas']));
});

test('[Poin 5] Pasien tidak bisa mengakses export milik admin (403)', async () => {
    const res = await dimas.goto('/admin/obat/export');
    expect(res.status()).toBe(403);
});

// ---------------------------------------------------------------------
// POIN 7 - GANTI PASSWORD
// ---------------------------------------------------------------------
async function gantiPassword(page, lama, baru, konfirmasi) {
    await page.goto('/profile');
    await page.fill('input[name=password_lama]', lama);
    await page.fill('input[name=password_baru]', baru);
    await page.fill('input[name=password_baru_confirmation]', konfirmasi);
    await page.click('button:has-text("Simpan Password")');
    await page.waitForLoadState('networkidle');
}

test('[Poin 7] Halaman Profile bisa diakses admin, dokter, dan pasien', async () => {
    for (const page of [admin, dokter, rina]) {
        const res = await page.goto('/profile');
        expect(res.status()).toBe(200);
        await expect(page.locator('input[name=password_lama]')).toBeVisible();
        await expect(page.locator('input[name=password_baru]')).toBeVisible();
        await expect(page.locator('input[name=password_baru_confirmation]')).toBeVisible();
    }
});

test('[Poin 7] Password lama salah -> error di bawah kolom', async () => {
    await gantiPassword(rina, 'salahsalah', PASSWORD_BARU, PASSWORD_BARU);
    await expect(rina.getByText('Password lama tidak sesuai.')).toBeVisible();
});

test('[Poin 7] Password baru kurang dari 8 karakter -> error', async () => {
    await gantiPassword(rina, AKUN.rina.password, 'pendek', 'pendek');
    await expect(rina.getByText('Password baru minimal 8 karakter.')).toBeVisible();
});

test('[Poin 7] Konfirmasi tidak sama -> error', async () => {
    await gantiPassword(rina, AKUN.rina.password, PASSWORD_BARU, 'berbeda123');
    await expect(rina.getByText('Konfirmasi password baru tidak sama.')).toBeVisible();
});

test('[Poin 7] Password baru sama dengan lama -> error', async () => {
    await gantiPassword(rina, AKUN.rina.password, AKUN.rina.password, AKUN.rina.password);
    await expect(rina.getByText('Password baru tidak boleh sama dengan password lama.')).toBeVisible();
});

test('[Poin 7] Password valid -> pesan sukses, login dengan password baru berhasil', async ({ browser }) => {
    await gantiPassword(rina, AKUN.rina.password, PASSWORD_BARU, PASSWORD_BARU);
    expect(await pesanFlash(rina)).toContain('Password berhasil diganti');

    const lamaGagal = await login(browser, AKUN.rina);              // password lama
    await expect(lamaGagal).toHaveURL(/\/login/);
    await lamaGagal.context().close();

    const baruBerhasil = await login(browser, AKUN.rina, PASSWORD_BARU);
    await expect(baruBerhasil).toHaveURL(/\/pasien\/dashboard/);
    await baruBerhasil.context().close();
});

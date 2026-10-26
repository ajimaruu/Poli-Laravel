// =====================================================================
// HELPER bersama untuk semua test
// =====================================================================
import { expect } from '@playwright/test';

// # Akun dari UserSeeder & DemoSeeder
export const AKUN = {
    admin: { email: 'admin@gmail.com', password: 'admin' },
    dokter: { email: 'budi@poli.test', password: 'password123', nama: 'dr. Budi Santoso' },
    rina: { email: 'rina@poli.test', password: 'password123', nama: 'Rina Amelia' },
    dimas: { email: 'dimas@poli.test', password: 'password123', nama: 'Dimas Pratama' },
};

// # Setiap user login di "browser context" sendiri = seperti jendela Incognito terpisah,
// # sehingga 4 orang bisa login bersamaan dalam 1 pengujian.
export async function login(browser, akun, password = akun.password) {
    const context = await browser.newContext();
    const page = await context.newPage();
    await page.goto('/login');
    await page.fill('input[name=email]', akun.email);
    await page.fill('input[name=password]', password);
    await page.click('button[type=submit]');
    await page.waitForLoadState('networkidle');
    return page;
}

// # Isi pesan flash (hijau/merah) di atas halaman
export async function pesanFlash(page) {
    const el = page.locator('#flash-message, .alert').first();
    return (await el.count()) ? (await el.innerText()).trim() : '';
}

// # Token CSRF dari halaman yang sedang dibuka (untuk mengirim request langsung)
export async function tokenCsrf(page) {
    if (!(await page.locator('input[name=_token]').count())) await page.goto('/profile');
    return page.locator('input[name=_token]').first().getAttribute('value');
}

// # Jumlah pada badge lonceng notifikasi (0 jika badge tidak tampil)
export async function jumlahLonceng(page) {
    const badge = page.locator('header .indicator .badge');
    return (await badge.count()) ? Number((await badge.innerText()).trim()) : 0;
}

// # Gambar PNG 1x1 piksel yang valid, dipakai sebagai "foto bukti bayar"
export const GAMBAR_PNG = {
    name: 'bukti-bayar.png',
    mimeType: 'image/png',
    buffer: Buffer.from(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
        'base64',
    ),
};

export async function bukaLonceng(page) {
    await page.click('header [aria-label=Notifikasi]');
    return page.locator('header .dropdown-content');
}

export { expect };

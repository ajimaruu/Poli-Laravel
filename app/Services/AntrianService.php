<?php

namespace App\Services;

use App\Models\DaftarPoli;

# SERVICE ANTRIAN
# Semua logika "nomor yang sedang dilayani" dikumpulkan di satu tempat,
# supaya Dashboard (saat halaman dimuat) dan Event real-time memakai rumus yang SAMA.
#
# DEFINISI "nomor yang sedang dilayani":
#   nomor antrian TERKECIL pada sebuah jadwal yang BELUM diperiksa dokter.
#   Contoh: antrian 1 sudah diperiksa, 2 dan 3 belum -> yang sedang dilayani = 2.
#   Begitu dokter menyimpan hasil periksa nomor 2, nomor yang dilayani otomatis maju ke 3.
class AntrianService
{
    # Nomor yang sedang dilayani untuk SATU jadwal.
    # Hasil null artinya tidak ada pasien yang sedang menunggu.
    public function nomorDilayani(int $idJadwal): ?int
    {
        $nomor = DaftarPoli::aktif()              # scope: belum punya data periksa
            ->where('id_jadwal', $idJadwal)
            ->min('no_antrian');

        return $nomor !== null ? (int) $nomor : null;
    }

    # Nomor yang sedang dilayani untuk BANYAK jadwal sekaligus, dalam SATU query
    # (GROUP BY id_jadwal). Dipakai tabel dashboard supaya tidak query per baris.
    # Hasil: [id_jadwal => nomor_dilayani], contoh [1 => 2, 3 => 1]
    public function nomorDilayaniSemua(array $idJadwal): array
    {
        return DaftarPoli::aktif()
            ->whereIn('id_jadwal', $idJadwal)
            ->groupBy('id_jadwal')
            ->selectRaw('id_jadwal, MIN(no_antrian) as nomor')
            ->pluck('nomor', 'id_jadwal')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    # Jumlah pasien yang masih menunggu di satu jadwal (termasuk yang sedang dilayani)
    public function jumlahMenunggu(int $idJadwal): int
    {
        return DaftarPoli::aktif()->where('id_jadwal', $idJadwal)->count();
    }

    # Nomor antrian berikutnya untuk pendaftar baru di sebuah jadwal.
    # Memakai MAX + 1 (bukan COUNT + 1) supaya tetap benar walaupun ada data terhapus.
    public function nomorBerikutnya(int $idJadwal): int
    {
        return (int) DaftarPoli::where('id_jadwal', $idJadwal)->max('no_antrian') + 1;
    }
}

<?php

namespace Database\Seeders;

use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

# DATA DEMO untuk presentasi. Jalankan dengan:
#   php artisan db:seed --class=DemoSeeder
# Aman dijalankan berkali-kali (firstOrCreate tidak membuat data ganda).
# Semua akun demo memakai password: password123
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        # ---------- Poli ----------
        $umum = Poli::firstOrCreate(['nama_poli' => 'Poli Umum'], ['keterangan' => 'Pemeriksaan umum']);
        $gigi = Poli::firstOrCreate(['nama_poli' => 'Poli Gigi'], ['keterangan' => 'Pemeriksaan gigi dan mulut']);
        $anak = Poli::firstOrCreate(['nama_poli' => 'Poli Anak'], ['keterangan' => 'Pemeriksaan anak']);

        # ---------- Dokter ----------
        $dokter = [
            ['dr. Budi Santoso', 'budi@poli.test', $umum],
            ['drg. Sari Lestari', 'sari@poli.test', $gigi],
            ['dr. Andi Wijaya, Sp.A', 'andi@poli.test', $anak],
        ];

        foreach ($dokter as $i => [$nama, $email, $poli]) {
            $d = User::firstOrCreate(['email' => $email], [
                'nama' => $nama,
                'alamat' => 'Semarang',
                'no_ktp' => '33740100000000' . ($i + 10),
                'no_hp' => '0812000000' . ($i + 10),
                'role' => 'dokter',
                'id_poli' => $poli->id,
                'password' => $password,
            ]);

            # Satu jadwal aktif per dokter
            JadwalPeriksa::firstOrCreate(
                ['id_dokter' => $d->id, 'hari' => ['Senin', 'Rabu', 'Jumat'][$i]],
                ['jam_mulai' => '08:00', 'jam_selesai' => '12:00', 'status' => true],
            );
        }

        # ---------- Obat ----------
        $obat = [
            ['Paracetamol 500mg', 'Tablet', 5000, 100],
            ['Amoxicillin 500mg', 'Kapsul', 12000, 50],
            ['OBH Sirup', 'Botol', 18000, 30],
            ['Vitamin C 1000mg', 'Tablet', 8000, 0], # sengaja stok 0 untuk demo "stok habis"
        ];
        foreach ($obat as [$nama, $kemasan, $harga, $stok]) {
            Obat::firstOrCreate(['nama_obat' => $nama], compact('kemasan', 'harga', 'stok'));
        }

        # ---------- Pasien ----------
        $pasien = [
            ['Rina Amelia', 'rina@poli.test'],
            ['Dimas Pratama', 'dimas@poli.test'],
            ['Siti Nurhaliza', 'siti@poli.test'],
        ];
        foreach ($pasien as $i => [$nama, $email]) {
            User::firstOrCreate(['email' => $email], [
                'nama' => $nama,
                'alamat' => 'Semarang',
                'no_ktp' => '33740200000000' . ($i + 10),
                'no_hp' => '0857000000' . ($i + 10),
                'no_rm' => now()->format('Ym') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'role' => 'pasien',
                'password' => $password,
            ]);
        }
    }
}

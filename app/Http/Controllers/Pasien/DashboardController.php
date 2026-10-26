<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Services\AntrianService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    # AntrianService otomatis "disuntikkan" oleh Laravel (dependency injection)
    public function index(AntrianService $antrian)
    {
        # 1) ANTRIAN AKTIF milik pasien yang login = pendaftaran yang belum diperiksa.
        #    Karena aturan "tidak boleh daftar 2 poli sekaligus", maksimal hanya ada 1.
        $antrianAktif = DaftarPoli::with('jadwalPeriksa.dokter.poli')
            ->aktif()
            ->where('id_pasien', Auth::id())
            ->latest()
            ->first();

        # 2) TABEL JADWAL semua poli & dokter (eager loading dokter + poli)
        $jadwals = JadwalPeriksa::with('dokter.poli')
            ->orderBy('id_dokter')
            ->orderBy('jam_mulai')
            ->get();

        # 3) Nomor yang sedang dilayani untuk SEMUA jadwal dalam 1 query
        $nomorDilayani = $antrian->nomorDilayaniSemua($jadwals->pluck('id')->all());

        return view('pasien.dashboard', compact('antrianAktif', 'jadwals', 'nomorDilayani'));
    }
}

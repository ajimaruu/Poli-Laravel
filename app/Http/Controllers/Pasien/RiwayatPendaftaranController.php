<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use Illuminate\Support\Facades\Auth;

class RiwayatPendaftaranController extends Controller
{
    public function index()
    {
        # EAGER LOADING dengan with():
        # Semua relasi yang dibutuhkan tabel diambil SEKALIGUS di awal.
        # Tanpa with(), setiap baris tabel akan memicu query baru untuk poli,
        # dokter, dan periksa (masalah N+1 query). Dengan with(), jumlah query
        # tetap sedikit berapa pun banyaknya baris riwayat.
        $riwayat = DaftarPoli::with([
                'jadwalPeriksa.dokter.poli', # jadwal -> dokter -> poli
                'periksa',                   # untuk status sudah/belum diperiksa
            ])
            ->where('id_pasien', Auth::id()) # HANYA milik pasien yang login
            ->latest()                       # urut dari pendaftaran terbaru
            ->get();

        return view('pasien.riwayat.index', compact('riwayat'));
    }

    public function show($id)
    {
        $daftar = DaftarPoli::with([
                'jadwalPeriksa.dokter.poli',
                'periksa.detailPeriksas.obat', # obat-obat yang diresepkan
            ])
            ->where('id_pasien', Auth::id())  # pasien lain tidak bisa membuka (hasilnya 404)
            ->findOrFail($id);

        # Detail hanya ada jika sudah diperiksa dokter
        if (! $daftar->periksa) {
            return redirect()
                ->route('pasien.riwayat.index')
                ->with('message', 'Pendaftaran ini belum diperiksa oleh dokter.')
                ->with('type', 'error');
        }

        return view('pasien.riwayat.show', compact('daftar'));
    }
}

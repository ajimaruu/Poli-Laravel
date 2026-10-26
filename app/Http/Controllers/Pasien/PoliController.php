<?php

namespace App\Http\Controllers\Pasien;

use App\Events\AntrianDiperbarui;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use App\Models\User;
use App\Services\AntrianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PoliController extends Controller
{
    public function get()
    {
        $user = Auth::user();
        $polis = Poli::all();
        $jadwal = JadwalPeriksa::with('dokter', 'dokter.poli')->get();

        # Antrian yang masih aktif (belum diperiksa). Dipakai view untuk
        # menampilkan peringatan dan mengunci form jika pasien sudah terdaftar.
        $antrianAktif = DaftarPoli::with('jadwalPeriksa.dokter.poli')
            ->aktif()
            ->where('id_pasien', $user->id)
            ->first();

        # Nomor rekam medis: urutan pasien ini di antara seluruh pasien (001, 002, ...)
        $urutan = User::where('role', 'pasien')
            ->where('id', '<=', $user->id)
            ->count();

        return view('pasien.daftar', [
            'user' => $user,
            'polis' => $polis,
            'jadwals' => $jadwal,
            'antrianAktif' => $antrianAktif,
            'noRm' => str_pad($urutan, 3, '0', STR_PAD_LEFT),
        ]);
    }

    public function submit(Request $request, AntrianService $antrian)
    {
        # id_pasien TIDAK lagi diambil dari form (hidden input bisa diubah lewat Inspect Element).
        # Pasien selalu diambil dari user yang sedang login.
        $request->validate([
            'id_poli' => 'required|exists:poli,id',
            'id_jadwal' => 'required|exists:jadwal_periksa,id',
            'keluhan' => 'required|string|max:1000', # kolom keluhan di database NOT NULL -> wajib diisi
        ], [
            'keluhan.required' => 'Keluhan wajib diisi.',
        ]);

        $idPasien = Auth::id();

        # DB::transaction = semua langkah di dalamnya dianggap satu paket.
        # lockForUpdate() mengunci baris yang dibaca sampai transaksi selesai, sehingga
        # 2 request bersamaan tidak bisa sama-sama lolos pengecekan / mendapat nomor yang sama.
        $hasil = DB::transaction(function () use ($request, $idPasien, $antrian) {

            # KETENTUAN: pasien tidak boleh daftar ke 2 poli sekaligus,
            # dan hanya boleh daftar lagi setelah pemeriksaan sebelumnya selesai.
            # "Selesai" = pendaftaran sebelumnya sudah punya data periksa.
            $masihAktif = DaftarPoli::aktif()
                ->where('id_pasien', $idPasien)
                ->lockForUpdate()
                ->exists();

            if ($masihAktif) {
                return null; # ditolak
            }

            # Kunci baris jadwal supaya nomor antrian tidak dobel
            JadwalPeriksa::whereKey($request->id_jadwal)->lockForUpdate()->first();

            return DaftarPoli::create([
                'id_pasien' => $idPasien,
                'id_jadwal' => $request->id_jadwal,
                'keluhan' => $request->keluhan,
                'no_antrian' => $antrian->nomorBerikutnya((int) $request->id_jadwal),
            ]);
        });

        if (! $hasil) {
            return redirect()
                ->back()
                ->with('message', 'Anda masih memiliki antrian aktif. Silakan selesaikan pemeriksaan terlebih dahulu.')
                ->with('type', 'error');
        }

        # REAL-TIME: beri tahu semua dashboard pasien bahwa antrian jadwal ini berubah
        AntrianDiperbarui::kirim($hasil->id_jadwal);

        return redirect()
            ->route('pasien.dashboard')
            ->with('message', "Berhasil mendaftar. Nomor antrian Anda: {$hasil->no_antrian}")
            ->with('type', 'success');
    }
}

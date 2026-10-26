<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use App\Notifications\PembayaranDiverifikasi;

class VerifikasiPembayaranController extends Controller
{
    public function index()
    {
        $relasi = ['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli'];

        # Tagihan yang sudah diupload pasien dan MENUNGGU dicek admin
        $menunggu = Periksa::with($relasi)
            ->where('status_bayar', Periksa::MENUNGGU_VERIFIKASI)
            ->oldest('tgl_upload_bukti') # yang upload lebih dulu, dicek lebih dulu
            ->get();

        # Riwayat pembayaran yang sudah lunas
        $lunas = Periksa::with($relasi)
            ->where('status_bayar', Periksa::LUNAS)
            ->latest('tgl_verifikasi')
            ->get();

        return view('admin.pembayaran.index', compact('menunggu', 'lunas'));
    }

    public function konfirmasi(Periksa $periksa)
    {
        # ANTI DOBEL: status hanya berubah jika saat ini MASIH menunggu_verifikasi.
        # Jika admin klik "Konfirmasi" 2x, klik kedua menghasilkan $berhasil = 0,
        # sehingga pasien tidak menerima notifikasi ganda.
        $berhasil = Periksa::whereKey($periksa->id)
            ->where('status_bayar', Periksa::MENUNGGU_VERIFIKASI)
            ->update([
                'status_bayar' => Periksa::LUNAS,
                'tgl_verifikasi' => now(),
            ]);

        if (! $berhasil) {
            return back()
                ->with('message', 'Pembayaran ini sudah diverifikasi atau belum diunggah.')
                ->with('type', 'error');
        }

        # NOTIFIKASI ke pasien pemilik tagihan: pembayarannya sudah lunas
        $periksa->refresh();
        $periksa->daftarPoli->pasien->notify(new PembayaranDiverifikasi($periksa));

        return back()
            ->with('message', 'Pembayaran berhasil dikonfirmasi. Status menjadi LUNAS.')
            ->with('type', 'success');
    }
}

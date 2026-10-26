<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use App\Notifications\PembayaranDiunggah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    # Daftar tagihan milik pasien yang login.
    # Tagihan = data periksa (hasil pemeriksaan dokter) beserta biayanya.
    public function index()
    {
        $tagihan = Periksa::with(['daftarPoli.jadwalPeriksa.dokter.poli'])
            # whereHas: ambil periksa yang pendaftarannya milik pasien ini saja
            ->whereHas('daftarPoli', fn ($q) => $q->where('id_pasien', Auth::id()))
            ->latest('tgl_periksa')
            ->get();

        return view('pasien.pembayaran.index', compact('tagihan'));
    }

    public function upload(Request $request, Periksa $periksa)
    {
        # 1) KEAMANAN: pastikan tagihan ini memang milik pasien yang login.
        #    Tanpa cek ini, pasien bisa mengganti id di URL dan upload ke tagihan orang lain.
        abort_if((int) $periksa->daftarPoli->id_pasien !== (int) Auth::id(), 403);

        # 2) VALIDASI FILE: wajib gambar jpg/jpeg/png, maksimal 2 MB
        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'bukti_bayar.required' => 'Foto bukti pembayaran wajib dipilih.',
            'bukti_bayar.image' => 'File harus berupa gambar.',
            'bukti_bayar.mimes' => 'Format gambar harus jpg, jpeg, atau png.',
            'bukti_bayar.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);

        # 3) Hanya tagihan yang BELUM BAYAR yang boleh diupload
        if ($periksa->status_bayar !== Periksa::BELUM_BAYAR) {
            return back()
                ->with('message', 'Bukti pembayaran untuk tagihan ini sudah pernah diunggah.')
                ->with('type', 'error');
        }

        # 4) Simpan file ke storage/app/public/bukti-bayar dengan nama acak (aman dari nama bentrok)
        $path = $request->file('bukti_bayar')->store('bukti-bayar', 'public');

        # 5) ANTI DOBEL: update hanya terjadi jika status MASIH belum_bayar.
        #    Jika tombol diklik 2x sangat cepat, request kedua mendapat $berhasil = 0
        #    sehingga notifikasi tidak terkirim dua kali.
        $berhasil = Periksa::whereKey($periksa->id)
            ->where('status_bayar', Periksa::BELUM_BAYAR)
            ->update([
                'bukti_bayar' => $path,
                'status_bayar' => Periksa::MENUNGGU_VERIFIKASI,
                'tgl_upload_bukti' => now(),
            ]);

        if (! $berhasil) {
            Storage::disk('public')->delete($path); # buang file yang tidak terpakai
            return back()
                ->with('message', 'Bukti pembayaran untuk tagihan ini sudah pernah diunggah.')
                ->with('type', 'error');
        }

        # 6) NOTIFIKASI ke dokter yang memeriksa pasien ini (satu kejadian = satu notifikasi)
        $periksa->refresh();
        $dokter = $periksa->daftarPoli->jadwalPeriksa->dokter;
        $dokter->notify(new PembayaranDiunggah($periksa));

        return back()
            ->with('message', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.')
            ->with('type', 'success');
    }
}

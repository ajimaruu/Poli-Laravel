<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

# Dipakai semua role. Setiap query SELALU dimulai dari $request->user()->notifications(),
# sehingga user hanya bisa melihat & menandai notifikasi MILIKNYA SENDIRI.
class NotifikasiController extends Controller
{
    # Halaman semua notifikasi, urut terbaru, 15 per halaman
    public function index(Request $request)
    {
        $notifikasi = $request->user()->notifications()->paginate(15);

        return view('notifikasi.index', compact('notifikasi'));
    }

    # Tandai SATU notifikasi sebagai sudah dibaca
    public function baca(Request $request, string $id)
    {
        # firstOrFail(): jika id notifikasi bukan milik user ini -> error 404.
        # Inilah yang mencegah user menandai notifikasi milik orang lain.
        $notif = $request->user()->notifications()->whereKey($id)->firstOrFail();

        $notif->markAsRead(); # mengisi kolom read_at dengan waktu sekarang

        return back();
    }

    # Tandai SEMUA notifikasi milik user ini sebagai sudah dibaca
    public function bacaSemua(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()
            ->with('message', 'Semua notifikasi ditandai sudah dibaca.')
            ->with('type', 'success');
    }
}

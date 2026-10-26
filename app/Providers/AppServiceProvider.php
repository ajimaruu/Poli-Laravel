<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        # VIEW COMPOSER untuk header (lonceng notifikasi).
        # Setiap kali header.blade.php dirender, fungsi ini dijalankan dulu dan
        # mengirim 2 variabel: daftar notifikasi terbaru & jumlah yang belum dibaca.
        # Keuntungannya: controller lain tidak perlu mengirim data notifikasi satu per satu.
        View::composer('components.partials.header', function ($view) {
            $user = Auth::user();

            $view->with([
                # notifications() dari trait Notifiable sudah otomatis:
                #  - hanya milik user ini (notifiable_id = id user yang login)
                #  - urut dari yang terbaru
                'notifikasiTerbaru' => $user ? $user->notifications()->take(8)->get() : collect(),
                'jumlahBelumDibaca' => $user ? $user->unreadNotifications()->count() : 0,
            ]);
        });
    }
}

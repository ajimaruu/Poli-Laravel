<?php

use App\Http\Controllers\Admin\AdminPoliController;
use App\Http\Controllers\Admin\DokterController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\PasienController;
use App\Http\Controllers\Admin\VerifikasiPembayaranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dokter\JadwalPeriksaController;
use App\Http\Controllers\Dokter\PeriksaPasienController;
use App\Http\Controllers\Dokter\RiwayatPasienController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\Pasien\DashboardController as PasienDashboardController;
use App\Http\Controllers\Pasien\PembayaranController;
use App\Http\Controllers\Pasien\PoliController as PasienPoliController;
use App\Http\Controllers\Pasien\RiwayatPendaftaranController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Semua role (admin, dokter, pasien) - cukup login
|--------------------------------------------------------------------------
| # Tidak memakai middleware role:..., karena Profile & Notifikasi
| # boleh diakses SEMUA role. Data yang ditampilkan selalu milik user yang login.
*/
Route::middleware('auth')->group(function () {
    # FITUR: Ganti Password
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    # FITUR: Notifikasi
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
    Route::post('/notifikasi/{id}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    # FITUR: Export Excel.
    # Route export WAJIB ditulis SEBELUM Route::resource. Kalau sesudahnya,
    # URL /admin/dokter/export akan dianggap route "show" dengan id = "export".
    Route::get('/dokter/export', [DokterController::class, 'export'])->name('dokter.export');
    Route::get('/pasien/export', [PasienController::class, 'export'])->name('pasien.export');
    Route::get('/obat/export', [ObatController::class, 'export'])->name('obat.export');

    Route::resource('polis', AdminPoliController::class);
    Route::resource('dokter', DokterController::class);
    Route::resource('pasien', PasienController::class);
    Route::resource('obat', ObatController::class);

    # FITUR: Verifikasi Pembayaran
    Route::get('/pembayaran', [VerifikasiPembayaranController::class, 'index'])->name('admin.pembayaran.index');
    Route::patch('/pembayaran/{periksa}/konfirmasi', [VerifikasiPembayaranController::class, 'konfirmasi'])->name('admin.pembayaran.konfirmasi');
});

/*
|--------------------------------------------------------------------------
| Dokter
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:dokter'])->prefix('dokter')->group(function () {
    Route::get('/dashboard', function () {
        return view('dokter.dashboard');
    })->name('dokter.dashboard');

    # FITUR: Export Excel (sebelum resource, alasan sama seperti di atas)
    Route::get('/jadwal-periksa/export', [JadwalPeriksaController::class, 'export'])->name('jadwal-periksa.export');

    Route::patch('jadwal-periksa/{jadwalPeriksa}/aktifkan', [JadwalPeriksaController::class, 'aktifkan'])
        ->name('jadwal-periksa.aktifkan');

    Route::resource('jadwal-periksa', JadwalPeriksaController::class);

    Route::get('/periksa-pasien', [PeriksaPasienController::class, 'index'])->name('periksa-pasien.index');
    Route::post('/periksa-pasien', [PeriksaPasienController::class, 'store'])->name('periksa-pasien.store');
    Route::get('/periksa-pasien/{id}', [PeriksaPasienController::class, 'create'])->name('periksa-pasien.create');

    # FITUR: Export Excel riwayat (sebelum /riwayat-pasien/{id})
    Route::get('/riwayat-pasien/export', [RiwayatPasienController::class, 'export'])->name('riwayat-pasien.export');
    Route::get('/riwayat-pasien', [RiwayatPasienController::class, 'index'])->name('riwayat-pasien.index');
    Route::get('/riwayat-pasien/{id}', [RiwayatPasienController::class, 'show'])->name('riwayat-pasien.show');
});

/*
|--------------------------------------------------------------------------
| Pasien
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:pasien'])->prefix('pasien')->group(function () {
    # FITUR: Dashboard Pasien (sebelumnya hanya teks sambutan)
    Route::get('/dashboard', [PasienDashboardController::class, 'index'])->name('pasien.dashboard');

    Route::get('/daftar', [PasienPoliController::class, 'get'])->name('pasien.daftar');
    Route::post('/daftar', [PasienPoliController::class, 'submit'])->name('pasien.daftar.submit');

    # FITUR: Riwayat Pendaftaran Poli
    Route::get('/riwayat', [RiwayatPendaftaranController::class, 'index'])->name('pasien.riwayat.index');
    Route::get('/riwayat/{id}', [RiwayatPendaftaranController::class, 'show'])->name('pasien.riwayat.show');

    # FITUR: Upload Bukti Pembayaran
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pasien.pembayaran.index');
    Route::post('/pembayaran/{periksa}/upload', [PembayaranController::class, 'upload'])->name('pasien.pembayaran.upload');
});

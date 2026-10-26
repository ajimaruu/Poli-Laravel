<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

# Controller ini dipakai BERSAMA oleh admin, dokter, dan pasien.
# Karena itu ia tidak diletakkan di folder Admin/Dokter/Pasien.
class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    # Parameter bertipe UpdatePasswordRequest -> Laravel otomatis menjalankan
    # validasi di class tersebut SEBELUM method ini dieksekusi.
    # Jika validasi gagal, user dikembalikan ke form beserta pesan error,
    # dan baris di bawah ini tidak pernah dijalankan.
    public function updatePassword(UpdatePasswordRequest $request)
    {
        # $request->user() = user yang sedang login.
        # Tidak ada id user dari form -> pengguna HANYA bisa mengubah password miliknya sendiri.
        $user = $request->user();

        # Hash::make() mengubah password menjadi hash bcrypt,
        # jadi yang tersimpan di database BUKAN teks biasa.
        $user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return redirect()
            ->route('profile.edit')
            ->with('message', 'Password berhasil diganti.')
            ->with('type', 'success');
    }
}

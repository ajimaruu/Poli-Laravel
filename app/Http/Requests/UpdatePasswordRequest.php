<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

# FORM REQUEST = class khusus untuk validasi.
# Validasi dipindah dari controller ke sini supaya controller tetap ringkas
# dan aturan validasi mudah dibaca. Soal meminta validasi di sisi server
# "misalnya melalui Form Request" -> inilah implementasinya.
class UpdatePasswordRequest extends FormRequest
{
    # Siapa yang boleh mengirim form ini?
    # Cukup user yang sudah login (route-nya sudah dilindungi middleware auth).
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            # current_password = rule bawaan Laravel yang mengecek input ini
            # COCOK dengan password user yang sedang login (dibandingkan dengan hash di database).
            'password_lama' => ['required', 'current_password'],

            'password_baru' => [
                'required',
                'string',
                'min:8',                    # minimal 8 karakter
                'confirmed',                # harus sama dengan field password_baru_confirmation
                'different:password_lama',  # tidak boleh sama dengan password lama
            ],
        ];
    }

    # Pesan error berbahasa Indonesia, tampil di bawah kolom yang salah
    public function messages(): array
    {
        return [
            'password_lama.required' => 'Password lama wajib diisi.',
            'password_lama.current_password' => 'Password lama tidak sesuai.',
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min' => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak sama.',
            'password_baru.different' => 'Password baru tidak boleh sama dengan password lama.',
        ];
    }
}

<x-layouts.app title="Profil">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Profil & Password</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ================= INFO AKUN ================= --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
            <div class="card-body p-8 items-center text-center gap-1">
                <div class="w-20 h-20 rounded-full bg-primary text-white flex items-center justify-center text-3xl font-bold mb-2">
                    {{ strtoupper(substr($user->nama, 0, 1)) }}
                </div>
                <h3 class="text-lg font-bold text-slate-800">{{ $user->nama }}</h3>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                <span class="mt-2 px-3 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-600 capitalize">
                    {{ $user->role }}
                </span>

                @if($user->role === 'pasien' && $user->no_rm)
                <p class="text-xs text-slate-500 mt-3">No. Rekam Medis</p>
                <p class="font-mono font-semibold text-slate-700">{{ $user->no_rm }}</p>
                @endif
            </div>
        </div>

        {{-- ================= FORM GANTI PASSWORD ================= --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 lg:col-span-2">
            <div class="card-body p-8">

                <h3 class="text-lg font-bold text-slate-800 mb-1">
                    <i class="fas fa-key text-primary mr-1"></i> Ganti Password
                </h3>
                <p class="text-sm text-slate-500 mb-6">
                    Password baru minimal 8 karakter dan tidak boleh sama dengan password lama.
                </p>

                {{-- # method PUT karena kita MENGUBAH data yang sudah ada --}}
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Password Lama --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Password Lama <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_lama" autocomplete="current-password"
                            class="w-full px-4 py-2 rounded-lg border-2 border-slate-300
                                   focus:border-primary focus:outline-none
                                   @error('password_lama') border-red-500 @enderror">
                        {{-- # @error menampilkan pesan error tepat di bawah kolom yang salah --}}
                        @error('password_lama')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password Baru --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_baru" autocomplete="new-password"
                            class="w-full px-4 py-2 rounded-lg border-2 border-slate-300
                                   focus:border-primary focus:outline-none
                                   @error('password_baru') border-red-500 @enderror">
                        @error('password_baru')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password Baru --}}
                    {{-- # Nama field WAJIB "password_baru_confirmation" agar rule "confirmed" bekerja --}}
                    <div class="mb-8">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Konfirmasi Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_baru_confirmation" autocomplete="new-password"
                            class="w-full px-4 py-2 rounded-lg border-2 border-slate-300
                                   focus:border-primary focus:outline-none
                                   @error('password_baru') border-red-500 @enderror">
                    </div>

                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90
                               text-white font-semibold text-sm transition">
                        <i class="fas fa-save mr-1"></i> Simpan Password
                    </button>
                </form>
            </div>
        </div>

    </div>

</x-layouts.app>

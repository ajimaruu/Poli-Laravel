<aside class="w-full bg-transparent text-slate-300 flex flex-col min-h-screen">

    {{-- # Role diambil dari user yang login, BUKAN dari URL.
         # Dulu memakai request()->is('admin*'), akibatnya halaman bersama
         # seperti /profile tidak menampilkan menu apa pun.
         # Sekarang menu selalu sesuai role, di halaman mana pun. --}}
    @php
        $role = auth()->user()->role;

        $baseLink = "flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-all duration-200";
        $inactive = "text-slate-300 hover:bg-white/10 hover:text-white";
        $active = "bg-gradient-to-r from-white/20 to-white/5 text-white font-semibold border border-indigo-400 border-2";
    @endphp

    {{-- ================= BRAND ================= --}}
    <div class="px-5 py-5 border-b border-white/10">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo-bengkot.png') }}" class="w-10 h-10 rounded-xl object-cover">

            <div>
                <div class="font-bold text-white text-lg leading-tight">
                    Poliklinik
                </div>

                @if($role === 'admin')
                <span
                    class="text-[10px] font-bold uppercase tracking-wider bg-indigo-400/20 text-indigo-300 border border-indigo-400/30 px-2 py-0.5 rounded-md">
                    Admin
                </span>
                @elseif($role === 'dokter')
                <span
                    class="text-[10px] font-bold uppercase tracking-wider bg-purple-400/20 text-purple-300 border border-purple-400/30 px-2 py-0.5 rounded-md">
                    Dokter
                </span>
                @elseif($role === 'pasien')
                <span
                    class="text-[10px] font-bold uppercase tracking-wider bg-amber-400/20 text-amber-300 border border-amber-400/30 px-2 py-0.5 rounded-md">
                    Pasien
                </span>
                @endif
            </div>
        </div>
    </div>


    {{-- ================= MENU ================= --}}
    <div class="flex-1 overflow-y-auto px-3 py-4">

        {{-- ================= ADMIN ================= --}}
        @if($role === 'admin')

        <p class="text-xs font-bold uppercase tracking-widest text-indigo-400 px-3 mb-3">
            Menu Admin
        </p>

        <div class="space-y-1">

            <a href="{{ route('admin.dashboard') }}"
                class="{{ $baseLink }} {{ request()->routeIs('admin.dashboard') ? $active : $inactive }}">
                <i class="fas fa-gauge-high w-4 text-center"></i>
                Dashboard Admin
            </a>

            <a href="{{ route('polis.index') }}"
                class="{{ $baseLink }} {{ request()->routeIs('polis.*') ? $active : $inactive }}">
                <i class="fas fa-hospital w-4 text-center"></i>
                Manajemen Poli
            </a>

            <a href="{{ route('dokter.index') }}"
                class="{{ $baseLink }} {{ request()->routeIs('dokter.*') ? $active : $inactive }}">
                <i class="fas fa-user-doctor w-4 text-center"></i>
                Manajemen Dokter
            </a>

            <a href="{{ route('pasien.index') }}"
                class="{{ $baseLink }} {{ request()->routeIs('pasien.*') ? $active : $inactive }}">
                <i class="fas fa-bed-pulse w-4 text-center"></i>
                Manajemen Pasien
            </a>

            <a href="{{ route('obat.index') }}"
                class="{{ $baseLink }} {{ request()->routeIs('obat.*') ? $active : $inactive }}">
                <i class="fas fa-pills w-4 text-center"></i>
                Manajemen Obat
            </a>

            {{-- # FITUR BARU: Verifikasi Pembayaran (Upload Bukti Pembayaran) --}}
            <a href="{{ route('admin.pembayaran.index') }}"
                class="{{ $baseLink }} {{ request()->routeIs('admin.pembayaran.*') ? $active : $inactive }}">
                <i class="fas fa-money-check-dollar w-4 text-center"></i>
                Verifikasi Pembayaran
            </a>
        </div>
        @endif


        {{-- ================= PASIEN ================= --}}
        @if($role === 'pasien')

        <p class="text-xs uppercase tracking-widest text-indigo-400 px-3 mb-3">
            Menu Pasien
        </p>

        <div class="space-y-1">

            <a href="{{ route('pasien.dashboard') }}"
                class="{{ $baseLink }} {{ request()->routeIs('pasien.dashboard') ? $active : $inactive }}">
                <i class="fas fa-house-medical w-4 text-center"></i>
                Dashboard Pasien
            </a>

            <a href="{{ route('pasien.daftar') }}"
                class="{{ $baseLink }} {{ request()->routeIs('pasien.daftar') ? $active : $inactive }}">
                <i class="fas fa-file-medical w-4 text-center"></i>
                Pendaftaran Periksa
            </a>

            {{-- # FITUR BARU: Riwayat Pendaftaran Poli --}}
            <a href="{{ route('pasien.riwayat.index') }}"
                class="{{ $baseLink }} {{ request()->routeIs('pasien.riwayat.*') ? $active : $inactive }}">
                <i class="fas fa-clock-rotate-left w-4 text-center"></i>
                Riwayat Pendaftaran
            </a>

            {{-- # FITUR BARU: Pembayaran (upload bukti bayar) --}}
            <a href="{{ route('pasien.pembayaran.index') }}"
                class="{{ $baseLink }} {{ request()->routeIs('pasien.pembayaran.*') ? $active : $inactive }}">
                <i class="fas fa-wallet w-4 text-center"></i>
                Pembayaran
            </a>
        </div>
        @endif


        {{-- ================= DOKTER ================= --}}
        @if($role === 'dokter')

        <p class="text-xs uppercase tracking-widest text-indigo-400 px-3 mb-3">
            Menu Dokter
        </p>

        <div class="space-y-1">

            <a href="{{ route('dokter.dashboard') }}"
                class="{{ $baseLink }} {{ request()->routeIs('dokter.dashboard') ? $active : $inactive }}">
                <i class="fas fa-stethoscope w-4 text-center"></i>
                Dashboard Dokter
            </a>

            <a href="{{ route('jadwal-periksa.index') }}"
                class="{{ $baseLink }} {{ request()->routeIs('jadwal-periksa.*') ? $active : $inactive }}">
                <i class="fas fa-calendar-alt w-4 text-center"></i>
                Jadwal Periksa
            </a>

            <a href="{{ route('periksa-pasien.index') }}"
                class="{{ $baseLink }} {{ request()->routeIs('periksa-pasien.*') ? $active : $inactive }}">
                <i class="fas fa-notes-medical w-4 text-center"></i>
                Periksa Pasien
            </a>

            <a href="{{ route('riwayat-pasien.index') }}"
                class="{{ $baseLink }} {{ request()->routeIs('riwayat-pasien.*') ? $active : $inactive }}">
                <i class="fas fa-clock-rotate-left w-4 text-center"></i>
                Riwayat Pasien
            </a>
        </div>
        @endif


        {{-- # Menu akun (Profil & Password, Notifikasi) dipindah ke dropdown profil di header --}}

    </div>


    {{-- ================= LOGOUT ================= --}}
    <div class="p-4 border-t border-white/10">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold transition-all">
                <i class="fas fa-right-from-bracket w-4"></i>
                Keluar
            </button>
        </form>
    </div>

</aside>
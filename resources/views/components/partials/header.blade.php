<header class="bg-base-100 border-b border-base-300 h-16 flex items-center px-6 gap-4 sticky top-0 z-30 shadow-sm">

    {{-- Mobile Hamburger --}}
    <button onclick="toggleSidebar()" class="btn btn-square btn-ghost lg:hidden">
        <i class="fas fa-bars w-5 h-5"></i>
    </button>

    {{-- Breadcrumb --}}
    <div class="flex-1">
        <div class="flex items-center gap-2 text-sm">
            <span class="text-base-content/50">Poliklinik</span>
            <i class="fas fa-chevron-right text-[10px] text-base-content/30"></i>
            <span class="font-semibold text-base-content">
                {{ $title ?? 'Dashboard' }}
            </span>
        </div>
    </div>

    {{-- ================= NOTIFIKASI (LONCENG) ================= --}}
    {{-- # $notifikasiTerbaru dan $jumlahBelumDibaca dikirim oleh View Composer
         # di AppServiceProvider, jadi header tidak perlu query sendiri di Blade.
         # Data diambil dari user yang login -> pengguna hanya melihat notifikasi miliknya. --}}
    <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="btn btn-ghost btn-circle" aria-label="Notifikasi">
            <div class="indicator">
                <i class="fas fa-bell text-lg text-slate-600"></i>

                {{-- # Badge angka hanya muncul jika ada notifikasi yang BELUM dibaca --}}
                @if($jumlahBelumDibaca > 0)
                <span class="badge badge-xs badge-error indicator-item text-white font-bold">
                    {{ $jumlahBelumDibaca > 9 ? '9+' : $jumlahBelumDibaca }}
                </span>
                @endif
            </div>
        </div>

        <div tabindex="0" class="dropdown-content z-50 mt-3 w-96 max-w-[90vw] rounded-2xl border border-slate-200 bg-base-100 shadow-xl">

            {{-- Judul dropdown + tombol tandai semua --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <span class="font-bold text-slate-800 text-sm">Notifikasi</span>

                @if($jumlahBelumDibaca > 0)
                <form action="{{ route('notifikasi.baca-semua') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-primary hover:underline">
                        Tandai semua dibaca
                    </button>
                </form>
                @endif
            </div>

            {{-- Daftar notifikasi (sudah urut dari yang terbaru) --}}
            <div class="max-h-96 overflow-y-auto">
                @forelse($notifikasiTerbaru as $notif)
                    @include('components.partials.notifikasi-item', ['notif' => $notif])
                @empty
                {{-- # Pesan kosong jika belum ada notifikasi --}}
                <div class="px-4 py-10 text-center text-slate-400 text-sm">
                    <i class="fas fa-bell-slash text-2xl mb-2 block"></i>
                    Belum ada notifikasi
                </div>
                @endforelse
            </div>

            <a href="{{ route('notifikasi.index') }}"
                class="block text-center text-xs font-semibold text-primary py-3 border-t border-slate-100 hover:bg-slate-50 rounded-b-2xl">
                Lihat semua notifikasi
            </a>
        </div>
    </div>

    {{-- Fullscreen --}}
    <button onclick="toggleFullscreen()" class="btn btn-square btn-ghost">
        <i id="fsIcon" class="fas fa-expand w-5 h-5"></i>
    </button>

    {{-- ================= DROPDOWN PROFIL ================= --}}
    {{-- # Klik nama/avatar -> muncul menu akun (Profil & Password, Notifikasi, Keluar).
         # Memakai komponen dropdown DaisyUI (tabindex + :focus), tanpa JavaScript tambahan. --}}
    <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" aria-label="Menu profil"
            class="flex items-center gap-3 rounded-xl px-2 py-1 hover:bg-slate-100 cursor-pointer transition">

            <div class="text-right hidden sm:block">
                {{-- # Kolom di tabel users bernama "nama", bukan "name". --}}
                <div class="text-sm font-semibold leading-tight">
                    {{ auth()->user()->nama ?? 'Pengguna' }}
                </div>
                <div class="text-xs text-base-content/50 leading-tight capitalize">
                    {{ auth()->user()->role }}
                </div>
            </div>

            <div class="avatar">
                <div class="w-10 h-10 rounded-full bg-primary text-primary-content flex items-center justify-center">
                    <span class="text-sm font-semibold leading-none">
                        {{ strtoupper(substr(auth()->user()->nama ?? 'U', 0, 1)) }}
                    </span>
                </div>
            </div>

            <i class="fas fa-chevron-down text-[10px] text-slate-400 hidden sm:block"></i>
        </div>

        <div tabindex="0" class="dropdown-content z-50 mt-3 w-64 rounded-2xl border border-slate-200 bg-base-100 shadow-xl overflow-hidden">

            {{-- Info akun --}}
            <div class="px-4 py-3 border-b border-slate-100">
                <div class="font-semibold text-slate-800 text-sm truncate">{{ auth()->user()->nama }}</div>
                <div class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</div>
            </div>

            <ul class="p-2 text-sm">
                <li>
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                        <i class="fas fa-user-gear w-4 text-center text-slate-400"></i>
                        Profil & Password
                    </a>
                </li>
                <li>
                    <a href="{{ route('notifikasi.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                        <i class="fas fa-bell w-4 text-center text-slate-400"></i>
                        <span class="flex-1">Notifikasi</span>
                        @if($jumlahBelumDibaca > 0)
                        <span class="badge badge-sm badge-error text-white">{{ $jumlahBelumDibaca }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            <div class="p-2 border-t border-slate-100">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-red-500 hover:bg-red-50">
                        <i class="fas fa-right-from-bracket w-4 text-center"></i>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

</header>

<script>
    function toggleFullscreen() {
        const icon = document.getElementById('fsIcon');

        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
            icon.classList.remove('fa-expand');
            icon.classList.add('fa-compress');
        } else {
            document.exitFullscreen();
            icon.classList.remove('fa-compress');
            icon.classList.add('fa-expand');
        }
    }
</script>
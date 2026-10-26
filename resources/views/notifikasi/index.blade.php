<x-layouts.app title="Notifikasi">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Notifikasi</h2>

        @if(auth()->user()->unreadNotifications()->exists())
        <form action="{{ route('notifikasi.baca-semua') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90
                       text-white text-sm font-semibold rounded-xl transition">
                <i class="fas fa-check-double text-xs"></i> Tandai semua dibaca
            </button>
        </form>
        @endif
    </div>

    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 overflow-hidden">
        {{-- # Komponen baris yang sama dengan dropdown lonceng dipakai ulang di sini --}}
        @forelse($notifikasi as $notif)
            @include('components.partials.notifikasi-item', ['notif' => $notif])
        @empty
        <div class="px-4 py-16 text-center text-slate-400">
            <i class="fas fa-bell-slash text-3xl mb-3 block"></i>
            Belum ada notifikasi
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $notifikasi->links() }}
    </div>

</x-layouts.app>

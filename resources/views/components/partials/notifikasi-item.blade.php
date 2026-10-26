{{-- # Satu baris notifikasi. Dipakai di dropdown lonceng DAN di halaman Notifikasi.
     # $notif adalah baris dari tabel "notifications".
     # $notif->data berisi array yang kita isi di method toDatabase() pada Notification class.
     # $notif->read_at bernilai null jika belum dibaca. --}}
@php
    $belumDibaca = is_null($notif->read_at);
@endphp

<div class="flex items-start gap-3 px-4 py-3 border-b border-slate-100 last:border-b-0
            {{ $belumDibaca ? 'bg-indigo-50/70' : 'bg-white' }}">

    {{-- # Pembeda visual: belum dibaca = ikon berwarna + titik biru + teks tebal --}}
    <div class="mt-0.5 w-8 h-8 shrink-0 rounded-full flex items-center justify-center
                {{ $belumDibaca ? 'bg-primary text-white' : 'bg-slate-100 text-slate-400' }}">
        <i class="fas {{ $notif->data['ikon'] ?? 'fa-bell' }} text-xs"></i>
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <p class="text-sm {{ $belumDibaca ? 'font-bold text-slate-800' : 'font-medium text-slate-500' }}">
                {{ $notif->data['judul'] ?? 'Notifikasi' }}
            </p>
            @if($belumDibaca)
            <span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>
            @endif
        </div>

        <p class="text-xs mt-0.5 {{ $belumDibaca ? 'text-slate-700' : 'text-slate-400' }}">
            {{ $notif->data['pesan'] ?? '' }}
        </p>

        {{-- # Waktu notifikasi, contoh: "5 menit yang lalu" --}}
        <p class="text-[11px] text-slate-400 mt-1">
            <i class="far fa-clock"></i>
            {{ $notif->created_at->locale('id')->diffForHumans() }}
        </p>
    </div>

    {{-- # Tombol tandai dibaca hanya muncul untuk notifikasi yang belum dibaca --}}
    @if($belumDibaca)
    <form action="{{ route('notifikasi.baca', $notif->id) }}" method="POST" class="shrink-0">
        @csrf
        <button type="submit" title="Tandai sudah dibaca"
            class="text-[11px] font-semibold text-primary hover:underline whitespace-nowrap">
            <i class="fas fa-check"></i> Dibaca
        </button>
    </form>
    @endif
</div>

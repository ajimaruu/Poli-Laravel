{{-- # Badge status pembayaran, dipakai di halaman pasien, admin, dan riwayat --}}
@php
    $warna = match ($periksa->status_bayar) {
        \App\Models\Periksa::LUNAS => 'bg-emerald-50 text-emerald-600',
        \App\Models\Periksa::MENUNGGU_VERIFIKASI => 'bg-amber-50 text-amber-600',
        default => 'bg-red-50 text-red-600',
    };
    $ikon = match ($periksa->status_bayar) {
        \App\Models\Periksa::LUNAS => 'fa-circle-check',
        \App\Models\Periksa::MENUNGGU_VERIFIKASI => 'fa-hourglass-half',
        default => 'fa-circle-exclamation',
    };
@endphp
<span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-lg {{ $warna }}">
    <i class="fas {{ $ikon }} text-xs"></i>
    {{ $periksa->labelStatusBayar() }}
</span>

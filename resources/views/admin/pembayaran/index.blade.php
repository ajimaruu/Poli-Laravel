<x-layouts.app title="Verifikasi Pembayaran">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Verifikasi Pembayaran</h2>
    </div>

    {{-- ================= MENUNGGU VERIFIKASI ================= --}}
    <h3 class="font-bold text-slate-700 mb-3">
        <i class="fas fa-hourglass-half text-amber-500 mr-1"></i>
        Menunggu Verifikasi
        <span class="ml-1 px-2 py-0.5 rounded-md bg-amber-100 text-amber-700 text-xs">{{ $menunggu->count() }}</span>
    </h3>

    <div class="card bg-base-100 shadow-md rounded-2 border mb-8">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Pasien</th>
                            <th class="px-6 py-4">Poli / Dokter</th>
                            <th class="px-6 py-4">Tagihan</th>
                            <th class="px-6 py-4">Diunggah</th>
                            <th class="px-6 py-4">Bukti</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700">
                        @forelse($menunggu as $item)
                        @php $dokter = $item->daftarPoli->jadwalPeriksa->dokter; @endphp
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $item->daftarPoli->pasien->nama ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="text-slate-700">{{ $dokter->poli->nama_poli ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $dokter->nama ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 font-bold">Rp {{ number_format($item->biaya_periksa, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-slate-500 text-xs">{{ $item->tgl_upload_bukti?->format('d/m/Y H:i') }}</td>

                            {{-- # Klik thumbnail -> buka modal berisi foto bukti ukuran besar --}}
                            <td class="px-6 py-4">
                                <button type="button" onclick="document.getElementById('bukti-{{ $item->id }}').showModal()">
                                    <img src="{{ asset('storage/' . $item->bukti_bayar) }}" alt="Bukti"
                                        class="w-14 h-14 object-cover rounded-lg border border-slate-200 hover:ring-2 hover:ring-primary">
                                </button>

                                <dialog id="bukti-{{ $item->id }}" class="modal">
                                    <div class="modal-box max-w-2xl">
                                        <h3 class="font-bold mb-3">Bukti Pembayaran - {{ $item->daftarPoli->pasien->nama ?? '' }}</h3>
                                        <img src="{{ asset('storage/' . $item->bukti_bayar) }}" alt="Bukti" class="w-full rounded-xl">
                                    </div>
                                    <form method="dialog" class="modal-backdrop"><button>tutup</button></form>
                                </dialog>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.pembayaran.konfirmasi', $item->id) }}" method="POST"
                                    onsubmit="return confirm('Konfirmasi pembayaran ini sebagai LUNAS?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-1 px-4 py-2
                                               bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold rounded-lg transition">
                                        <i class="fas fa-check text-xs"></i> Konfirmasi Lunas
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-400">
                                <i class="fas fa-inbox text-3xl mb-3 block"></i>
                                Tidak ada pembayaran yang menunggu verifikasi
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= SUDAH LUNAS ================= --}}
    <h3 class="font-bold text-slate-700 mb-3">
        <i class="fas fa-circle-check text-emerald-500 mr-1"></i> Sudah Lunas
    </h3>

    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Pasien</th>
                            <th class="px-6 py-4">Poli</th>
                            <th class="px-6 py-4">Tagihan</th>
                            <th class="px-6 py-4">Diverifikasi</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Bukti</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700">
                        @forelse($lunas as $item)
                        <tr class="border-t border-slate-100">
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $item->daftarPoli->pasien->nama ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}</td>
                            <td class="px-6 py-4">Rp {{ number_format($item->biaya_periksa, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-slate-500 text-xs">{{ $item->tgl_verifikasi?->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">@include('components.partials.badge-status-bayar', ['periksa' => $item])</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ asset('storage/' . $item->bukti_bayar) }}" target="_blank" class="text-xs font-semibold text-primary hover:underline">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-400">Belum ada pembayaran lunas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-layouts.app>

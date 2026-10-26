<x-layouts.app title="Detail Pemeriksaan">

    @php
        $jadwal = $daftar->jadwalPeriksa;
        $periksa = $daftar->periksa;
        # Total harga obat dihitung dari relasi yang sudah di-eager-load (tanpa query tambahan)
        $totalObat = $periksa->detailPeriksas->sum(fn ($d) => $d->obat->harga ?? 0);
    @endphp

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('pasien.riwayat.index') }}" class="flex items-center justify-center w-9 h-9
                  rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Detail Pemeriksaan</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ================= INFO PENDAFTARAN ================= --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
            <div class="card-body p-6 text-sm space-y-3">
                <h3 class="font-bold text-slate-800 mb-1">Informasi Pendaftaran</h3>

                <div>
                    <p class="text-xs text-slate-400">Poli</p>
                    <p class="font-semibold text-slate-700">{{ $jadwal->dokter->poli->nama_poli ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Dokter</p>
                    <p class="font-semibold text-slate-700">{{ $jadwal->dokter->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Jadwal</p>
                    <p class="font-semibold text-slate-700">
                        {{ $jadwal->hari }}, {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">No Antrian</p>
                    <p class="font-bold text-primary text-lg">{{ $daftar->no_antrian }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Keluhan</p>
                    <p class="text-slate-700">{{ $daftar->keluhan ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Tanggal Periksa</p>
                    <p class="font-semibold text-slate-700">{{ $periksa->tgl_periksa->translatedFormat('d F Y, H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- ================= HASIL PEMERIKSAAN ================= --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 lg:col-span-2">
            <div class="card-body p-6 text-sm">

                {{-- Catatan dokter --}}
                <h3 class="font-bold text-slate-800 mb-2">
                    <i class="fas fa-notes-medical text-primary mr-1"></i> Catatan Dokter
                </h3>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 mb-6">
                    {{ $periksa->catatan ?: 'Tidak ada catatan.' }}
                </div>

                {{-- Daftar obat --}}
                <h3 class="font-bold text-slate-800 mb-2">
                    <i class="fas fa-pills text-primary mr-1"></i> Daftar Obat
                </h3>
                <table class="table w-full mb-6">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Nama Obat</th>
                            <th class="px-4 py-3">Kemasan</th>
                            <th class="px-4 py-3 text-right">Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periksa->detailPeriksas as $i => $detail)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium">{{ $detail->obat->nama_obat ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $detail->obat->kemasan ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($detail->obat->harga ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-400">Tidak ada obat</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Rincian biaya --}}
                <div class="rounded-xl border border-slate-200 p-4 space-y-2">
                    <div class="flex justify-between text-slate-600">
                        <span>Total harga obat</span>
                        <span>Rp {{ number_format($totalObat, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Jasa dokter</span>
                        <span>Rp {{ number_format(\App\Models\Periksa::BIAYA_JASA_DOKTER, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-slate-800 text-base border-t border-slate-200 pt-2">
                        <span>Total Biaya</span>
                        <span>Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-1">
                        <span class="text-slate-500">Status pembayaran</span>
                        @include('components.partials.badge-status-bayar', ['periksa' => $periksa])
                    </div>
                </div>

            </div>
        </div>

    </div>

</x-layouts.app>

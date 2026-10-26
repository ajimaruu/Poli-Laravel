<x-layouts.app title="Riwayat Pendaftaran">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Riwayat Pendaftaran Poli</h2>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">

                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Poli</th>
                            <th class="px-6 py-4">Dokter</th>
                            <th class="px-6 py-4">Jadwal</th>
                            <th class="px-6 py-4">No Antrian</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="text-sm text-slate-700">
                        @forelse($riwayat as $item)
                        @php
                            $jadwal = $item->jadwalPeriksa;
                            $sudahDiperiksa = $item->periksa !== null;
                        @endphp
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $jadwal->dokter->poli->nama_poli ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $jadwal->dokter->nama ?? '-' }}
                            </td>

                            {{-- # Tanggal jadwal = hari & jam praktik + tanggal pasien mendaftar --}}
                            <td class="px-6 py-4 text-slate-600">
                                <div class="font-medium text-slate-700">
                                    {{ $jadwal->hari }}, {{ $item->created_at->translatedFormat('d M Y') }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="font-bold text-primary text-base">{{ $item->no_antrian }}</span>
                            </td>

                            <td class="px-6 py-4">
                                @if($sudahDiperiksa)
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-50 text-emerald-600 text-xs font-semibold rounded-lg">
                                    <i class="fas fa-circle-check text-xs"></i> Sudah Diperiksa
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-50 text-amber-600 text-xs font-semibold rounded-lg">
                                    <i class="fas fa-hourglass-half text-xs"></i> Menunggu
                                </span>
                                @endif
                            </td>

                            {{-- # Tombol Detail HANYA muncul jika sudah diperiksa --}}
                            <td class="px-6 py-4 text-right">
                                @if($sudahDiperiksa)
                                <a href="{{ route('pasien.riwayat.show', $item->id) }}" class="inline-flex items-center gap-1 px-4 py-2
                                          bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition">
                                    <i class="fas fa-eye text-xs"></i> Detail
                                </a>
                                @else
                                <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <i class="fas fa-inbox text-3xl mb-3 block"></i>
                                Belum ada riwayat pendaftaran
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>

</x-layouts.app>

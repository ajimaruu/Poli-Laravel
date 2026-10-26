<x-layouts.app title="Riwayat Pasien">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Riwayat Pasien</h2>

        {{-- # FITUR BARU: tombol Export Excel --}}
        @include('components.partials.tombol-export', ['url' => route('riwayat-pasien.export')])
    </div>

    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4">No Antrian</th>
                            <th class="px-6 py-4">Nama Pasien</th>
                            <th class="px-6 py-4">Keluhan</th>
                            <th class="px-6 py-4">Tanggal Periksa</th>
                            <th class="px-6 py-4">Biaya</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700">
                        @forelse($riwayat as $item)
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-slate-600">{{ $item->daftarPoli->no_antrian }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $item->daftarPoli->pasien->nama ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $item->daftarPoli->keluhan ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ \Carbon\Carbon::parse($item->tgl_periksa)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-slate-600">Rp {{ number_format($item->biaya_periksa, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('riwayat-pasien.show', $item->id) }}" class="inline-flex items-center gap-1 px-4 py-2
                                          bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition">
                                    <i class="fas fa-eye text-xs"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <i class="fas fa-inbox text-3xl mb-3 block"></i>
                                Belum ada riwayat pemeriksaan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-layouts.app>

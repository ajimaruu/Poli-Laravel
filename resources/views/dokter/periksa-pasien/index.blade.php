<x-layouts.app title="Periksa Pasien">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Periksa Pasien
        </h2>
    </div>

    {{-- Card --}}
    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">

            <div class="overflow-x-auto">
                <table class="table w-full">

                    {{-- Table Head --}}
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Pasien</th>
                            <th class="px-6 py-4">Keluhan</th>
                            <th class="px-6 py-4">No Antrian</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Table Body --}}
                    <tbody class="text-sm text-slate-700">
                        @forelse($daftarPasien as $item)
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">

                            <td class="px-6 py-4 text-slate-600">
                                {{ $item->id }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $item->pasien->nama ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $item->keluhan ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $item->no_antrian }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">

                                    @if($item->periksas->count() > 0)
                                    {{-- Sudah diperiksa --}}
                                    <span class="inline-flex items-center gap-1 px-3 py-1
                                                 bg-emerald-50 text-emerald-600
                                                 text-xs font-semibold rounded-lg">
                                        <i class="fas fa-circle-check text-xs"></i>
                                        Sudah Diperiksa
                                    </span>
                                    @else
                                    {{-- Tombol periksa --}}
                                    <a href="{{ route('periksa-pasien.create', $item->id) }}" class="inline-flex items-center gap-1 px-4 py-2
                                              bg-amber-500 hover:bg-amber-600
                                              text-white text-xs font-semibold
                                              rounded-lg transition">
                                        <i class="fas fa-stethoscope text-xs"></i>
                                        Periksa
                                    </a>
                                    @endif

                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-slate-400">
                                <i class="fas fa-inbox text-3xl mb-3 block"></i>
                                Belum ada pasien yang mendaftar
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</x-layouts.app>
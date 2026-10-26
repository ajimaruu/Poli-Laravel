<x-layouts.app title="Pembayaran">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Pembayaran</h2>
    </div>

    {{-- Petunjuk singkat alur pembayaran --}}
    <div class="mb-6 px-5 py-4 rounded-xl bg-indigo-50 border border-indigo-100 text-sm text-indigo-800 flex gap-3">
        <i class="fas fa-circle-info mt-0.5"></i>
        <div>
            Lakukan pembayaran sesuai total tagihan, lalu unggah foto bukti pembayaran (jpg/png, maks. 2 MB).
            Status akan berubah menjadi <b>Lunas</b> setelah diverifikasi admin.
        </div>
    </div>

    {{-- Error validasi file ditampilkan di atas tabel --}}
    @error('bukti_bayar')
    <div class="mb-4 px-5 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold">
        <i class="fas fa-circle-exclamation mr-1"></i> {{ $message }}
    </div>
    @enderror

    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">

                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Tanggal Periksa</th>
                            <th class="px-6 py-4">Poli / Dokter</th>
                            <th class="px-6 py-4">Total Tagihan</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="text-sm text-slate-700">
                        @forelse($tagihan as $item)
                        @php $dokter = $item->daftarPoli->jadwalPeriksa->dokter; @endphp
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">

                            <td class="px-6 py-4 text-slate-600">
                                {{ $item->tgl_periksa->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $dokter->poli->nama_poli ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $dokter->nama ?? '-' }}</div>
                            </td>

                            <td class="px-6 py-4 font-bold text-slate-800">
                                Rp {{ number_format($item->biaya_periksa, 0, ',', '.') }}
                            </td>

                            <td class="px-6 py-4">
                                @include('components.partials.badge-status-bayar', ['periksa' => $item])
                            </td>

                            <td class="px-6 py-4 text-right">
                                @if($item->status_bayar === \App\Models\Periksa::BELUM_BAYAR)

                                {{-- # Form upload. enctype="multipart/form-data" WAJIB agar file ikut terkirim --}}
                                <form action="{{ route('pasien.pembayaran.upload', $item->id) }}" method="POST"
                                    enctype="multipart/form-data" class="flex items-center justify-end gap-2">
                                    @csrf
                                    <input type="file" name="bukti_bayar" accept="image/png,image/jpeg" required
                                        class="file-input file-input-sm file-input-bordered w-56">
                                    <button type="submit" class="inline-flex items-center gap-1 px-4 py-2
                                               bg-primary hover:bg-primary/90 text-white text-xs font-semibold rounded-lg transition">
                                        <i class="fas fa-upload text-xs"></i> Upload
                                    </button>
                                </form>

                                @else
                                {{-- Sudah upload: tampilkan tombol lihat bukti --}}
                                <a href="{{ asset('storage/' . $item->bukti_bayar) }}" target="_blank"
                                    class="inline-flex items-center gap-1 px-4 py-2 bg-slate-100 hover:bg-slate-200
                                           text-slate-600 text-xs font-semibold rounded-lg transition">
                                    <i class="fas fa-image text-xs"></i> Lihat Bukti
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-slate-400">
                                <i class="fas fa-receipt text-3xl mb-3 block"></i>
                                Belum ada tagihan. Tagihan muncul setelah Anda diperiksa dokter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>

</x-layouts.app>

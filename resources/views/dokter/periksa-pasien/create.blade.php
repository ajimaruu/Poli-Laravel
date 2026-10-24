<x-layouts.app title="Periksa Pasien">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('periksa-pasien.index') }}" class="flex items-center justify-center w-9 h-9
                  rounded-lg bg-slate-100 hover:bg-slate-200
                  text-slate-600 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>

        <h2 class="text-2xl font-bold text-slate-800">
            Periksa Pasien
        </h2>
    </div>

    {{-- Card --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-8">

            <form action="{{ route('periksa-pasien.store') }}" method="POST">
                @csrf

                {{-- id daftar poli yang sedang diperiksa --}}
                <input type="hidden" name="id_daftar_poli" value="{{ $id }}">

                {{-- data yang dikirim ke controller --}}
                <input type="hidden" name="obat_json" id="obat_json" value="[]">
                <input type="hidden" name="biaya_periksa" id="biaya_periksa" value="0">

                {{-- Pilih Obat --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Pilih Obat <span class="text-red-500">*</span>
                    </label>
                    <select id="pilih_obat"
                        class="w-full px-4 py-2 rounded-lg border-2 border-slate-300
                               focus:border-primary focus:outline-none
                               @error('obat_json') border-red-500 @enderror">
                        <option value="">-- Pilih Obat --</option>
                        @foreach($obats as $obat)
                        <option value="{{ $obat->id }}"
                                data-nama="{{ $obat->nama_obat }}"
                                data-harga="{{ $obat->harga }}">
                            {{ $obat->nama_obat }} ({{ $obat->kemasan }}) - Rp {{ number_format($obat->harga, 0, ',', '.') }}
                        </option>
                        @endforeach
                    </select>
                    @error('obat_json')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Obat Terpilih --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-primary mb-2">
                        Obat Terpilih
                    </label>

                    <div id="daftar_obat" class="flex flex-col gap-2"></div>

                    <p id="obat_kosong" class="text-xs text-slate-400">
                        Belum ada obat yang dipilih.
                    </p>
                </div>

                {{-- Total Harga --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Total Harga
                    </label>
                    <div id="total_harga"
                         class="w-full px-4 py-2 rounded-lg bg-slate-100
                                text-slate-700 font-semibold">
                        Rp 0
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Catatan <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea name="catatan" rows="4" placeholder="Masukkan catatan..."
                        class="w-full px-4 py-2 rounded-lg border-2 border-slate-300
                               focus:border-primary focus:outline-none
                               @error('catatan') border-red-500 @enderror">{{ old('catatan') }}</textarea>
                    @error('catatan')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary
                               hover:bg-primary/90 text-white
                               font-semibold text-sm transition">
                        <i class="fas fa-save mr-1"></i>
                        Simpan
                    </button>

                    <a href="{{ route('periksa-pasien.index') }}" class="px-6 py-2.5 rounded-xl bg-slate-100
                              hover:bg-slate-200 text-slate-600
                              font-semibold text-sm transition">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>

    {{-- Script: tambah obat, hapus obat, hitung total --}}
    <script>
        // Daftar obat yang sudah dipilih dokter.
        let obatTerpilih = [];

        const selectObat = document.getElementById('pilih_obat');
        const daftarObat = document.getElementById('daftar_obat');
        const obatKosong = document.getElementById('obat_kosong');
        const totalHarga = document.getElementById('total_harga');
        const inputObatJson = document.getElementById('obat_json');
        const inputBiaya = document.getElementById('biaya_periksa');

        // Tambah obat saat dropdown dipilih.
        selectObat.addEventListener('change', function () {
            const option = this.options[this.selectedIndex];
            if (!this.value) return;

            // Cegah obat yang sama ditambahkan dua kali.
            const sudahAda = obatTerpilih.some(o => o.id === this.value);
            if (!sudahAda) {
                obatTerpilih.push({
                    id: this.value,
                    nama: option.dataset.nama,
                    harga: parseInt(option.dataset.harga),
                });
                render();
            }

            this.value = '';
        });

        // Hapus obat dari daftar.
        function hapusObat(id) {
            obatTerpilih = obatTerpilih.filter(o => o.id !== id);
            render();
        }

        // Gambar ulang daftar obat, total harga, dan input tersembunyi.
        function render() {
            daftarObat.innerHTML = '';
            let total = 0;

            obatTerpilih.forEach(function (obat) {
                total += obat.harga;

                const baris = document.createElement('div');
                baris.className = 'flex items-center justify-between px-4 py-2 ' +
                                  'bg-slate-50 border border-slate-200 rounded-lg';
                baris.innerHTML =
                    '<span class="text-sm text-slate-700">' + obat.nama +
                    ' <span class="text-slate-400">- Rp ' +
                    obat.harga.toLocaleString('id-ID') + '</span></span>';

                const tombol = document.createElement('button');
                tombol.type = 'button';
                tombol.className = 'inline-flex items-center gap-1 px-3 py-1 ' +
                                   'bg-red-500 hover:bg-red-600 text-white ' +
                                   'text-xs font-semibold rounded-lg transition';
                tombol.innerHTML = '<i class="fas fa-trash text-xs"></i> Hapus';
                tombol.onclick = function () { hapusObat(obat.id); };

                baris.appendChild(tombol);
                daftarObat.appendChild(baris);
            });

            obatKosong.style.display = obatTerpilih.length ? 'none' : 'block';
            totalHarga.textContent = 'Rp ' + total.toLocaleString('id-ID');

            // Nilai yang dibaca controller saat store().
            inputObatJson.value = JSON.stringify(obatTerpilih.map(o => o.id));
            inputBiaya.value = total;
        }

        render();
    </script>

</x-layouts.app>
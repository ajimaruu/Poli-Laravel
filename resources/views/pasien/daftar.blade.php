<x-layouts.app title="Daftar Poli">

    <div class="flex justify-center">
        <div class="w-full max-w-xl">

            {{-- Card --}}
            <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
                <div class="card-body p-8">

                    <h2 class="text-xl font-bold text-slate-800 text-center mb-6">
                        <i class="fas fa-house-medical text-primary mr-1"></i>
                        Pendaftaran Poli
                    </h2>

                    {{-- # KETENTUAN DASHBOARD: pasien yang masih punya antrian aktif tidak boleh daftar lagi.
                         # Form tetap tampil tetapi dikunci (fieldset disabled), dan server juga menolak. --}}
                    @if($antrianAktif)
                    <div class="mb-6 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                        <p class="font-semibold mb-1"><i class="fas fa-triangle-exclamation mr-1"></i> Anda masih memiliki antrian aktif</p>
                        <p>
                            {{ $antrianAktif->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }} &middot;
                            No. antrian <b>{{ $antrianAktif->no_antrian }}</b>.
                            Anda dapat mendaftar lagi setelah pemeriksaan selesai.
                        </p>
                        <a href="{{ route('pasien.dashboard') }}" class="inline-block mt-2 font-semibold underline">Lihat antrian</a>
                    </div>
                    @endif

                    <form action="{{ route('pasien.daftar.submit') }}" method="POST">
                        @csrf
                        <fieldset @disabled($antrianAktif) class="disabled:opacity-60">

                        {{-- Nomor Rekam Medis (tampilan saja) --}}
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                Nomor Rekam Medis
                            </label>
                            <input type="text" value="{{ $noRm }}" readonly
                                class="w-full px-4 py-2 rounded-lg border-2 border-slate-200
                                       bg-slate-100 text-slate-500 focus:outline-none">
                        </div>

                        {{-- Pilih Poli --}}
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                Pilih Poli <span class="text-red-500">*</span>
                            </label>
                            <select name="id_poli" id="id_poli"
                                class="w-full px-4 py-2 rounded-lg border-2 border-slate-300
                                       focus:border-primary focus:outline-none
                                       @error('id_poli') border-red-500 @enderror"
                                required>
                                <option value="">-- Pilih Poli --</option>
                                @foreach($polis as $poli)
                                <option value="{{ $poli->id }}" @selected(old('id_poli') == $poli->id)>
                                    {{ $poli->nama_poli }}
                                </option>
                                @endforeach
                            </select>
                            @error('id_poli')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Pilih Jadwal Periksa --}}
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                Pilih Jadwal Periksa <span class="text-red-500">*</span>
                            </label>
                            <select name="id_jadwal" id="id_jadwal"
                                class="w-full px-4 py-2 rounded-lg border-2 border-slate-300
                                       focus:border-primary focus:outline-none
                                       @error('id_jadwal') border-red-500 @enderror"
                                required>
                                <option value="">-- Pilih Jadwal --</option>
                                @foreach($jadwals as $jadwal)
                                <option value="{{ $jadwal->id }}"
                                        data-poli="{{ $jadwal->dokter->id_poli ?? '' }}"
                                        @selected(old('id_jadwal') == $jadwal->id)>
                                    {{ $jadwal->dokter->nama ?? 'Dokter' }} -
                                    {{ $jadwal->hari }}
                                    ({{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }})
                                </option>
                                @endforeach
                            </select>
                            @error('id_jadwal')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Keluhan --}}
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                Keluhan
                            </label>
                            <textarea name="keluhan" rows="3" placeholder="Tulis keluhan anda..."
                                class="w-full px-4 py-2 rounded-lg border-2 border-slate-300
                                       focus:border-primary focus:outline-none
                                       @error('keluhan') border-red-500 @enderror">{{ old('keluhan') }}</textarea>
                            @error('keluhan')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Button --}}
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary
                                       hover:bg-primary/90 text-white
                                       font-semibold text-sm transition">
                                Daftar Poli
                            </button>
                        </div>

                        </fieldset>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- Script: jadwal menyesuaikan poli yang dipilih --}}
    <script>
        const selectPoli = document.getElementById('id_poli');
        const selectJadwal = document.getElementById('id_jadwal');

        // Simpan semua option jadwal supaya bisa ditampilkan ulang.
        const semuaJadwal = Array.from(selectJadwal.options).slice(1);

        function filterJadwal() {
            const idPoli = selectPoli.value;

            // Kosongkan dropdown, sisakan placeholder.
            selectJadwal.length = 1;

            semuaJadwal.forEach(function (option) {
                if (idPoli && option.dataset.poli === idPoli) {
                    selectJadwal.appendChild(option);
                }
            });

            selectJadwal.options[0].textContent = idPoli
                ? '-- Pilih Jadwal --'
                : '-- Pilih poli terlebih dahulu --';
        }

        selectPoli.addEventListener('change', filterJadwal);
        filterJadwal();
    </script>

</x-layouts.app>

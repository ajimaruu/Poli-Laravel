<x-layouts.app title="Dashboard Pasien">

    <div class="flex items-center justify-between mb-6 gap-3 flex-wrap">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Halo, {{ auth()->user()->nama }} 👋</h2>
            <p class="text-sm text-slate-500">Pantau antrian poliklinik secara langsung.</p>
        </div>

        {{-- # Indikator koneksi WebSocket (diubah oleh JavaScript di bawah) --}}
        <span id="status-live" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">
            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
            Menghubungkan...
        </span>
    </div>

    {{-- ================= BANNER ANTRIAN AKTIF ================= --}}
    @if($antrianAktif)
    @php
        $jadwalSaya = $antrianAktif->jadwalPeriksa;
        $dilayaniSaya = $nomorDilayani[$jadwalSaya->id] ?? null;
    @endphp

    {{-- # data-jadwal & data-nomor-saya dibaca JavaScript untuk menghitung status secara live --}}
    <div id="banner-antrian"
        data-jadwal="{{ $jadwalSaya->id }}"
        data-nomor-saya="{{ $antrianAktif->no_antrian }}"
        class="rounded-2xl p-6 mb-8 text-white shadow-lg"
        style="background: linear-gradient(135deg, #1e2d6b 0%, #2d4499 60%, #1a2d7a 100%);">

        <div class="flex flex-col lg:flex-row lg:items-center gap-6">

            {{-- Info poli, dokter, jadwal --}}
            <div class="flex-1">
                <p class="text-xs uppercase tracking-widest text-indigo-200 font-semibold mb-2">Antrian Aktif Anda</p>
                <h3 class="text-2xl font-bold">{{ $jadwalSaya->dokter->poli->nama_poli ?? '-' }}</h3>
                <p class="text-indigo-100 mt-1">
                    <i class="fas fa-user-doctor mr-1"></i> {{ $jadwalSaya->dokter->nama ?? '-' }}
                </p>
                <p class="text-indigo-100 text-sm mt-1">
                    <i class="fas fa-calendar-day mr-1"></i>
                    {{ $jadwalSaya->hari }}, {{ substr($jadwalSaya->jam_mulai, 0, 5) }} - {{ substr($jadwalSaya->jam_selesai, 0, 5) }}
                </p>

                {{-- # Pesan status: "Giliran Anda" / "x antrian lagi" (diperbarui live) --}}
                <p id="pesan-status" class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/15 text-sm font-semibold">
                    @if($dilayaniSaya === $antrianAktif->no_antrian)
                        <i class="fas fa-bell"></i> Giliran Anda! Silakan menuju ruang periksa.
                    @elseif($dilayaniSaya !== null && $dilayaniSaya < $antrianAktif->no_antrian)
                        <i class="fas fa-hourglass-half"></i> {{ $antrianAktif->no_antrian - $dilayaniSaya }} antrian lagi sebelum giliran Anda
                    @else
                        <i class="fas fa-hourglass-half"></i> Menunggu antrian dimulai
                    @endif
                </p>
            </div>

            {{-- Dua kotak angka besar --}}
            <div class="flex gap-4">
                <div class="w-36 rounded-2xl bg-white/10 border border-white/20 p-4 text-center">
                    <p class="text-xs text-indigo-200 font-semibold">Nomor Anda</p>
                    <p class="text-5xl font-extrabold mt-1">{{ $antrianAktif->no_antrian }}</p>
                </div>

                <div class="w-36 rounded-2xl bg-white text-slate-800 p-4 text-center">
                    <p class="text-xs text-slate-500 font-semibold">Sedang Dilayani</p>
                    {{-- # data-dilayani = id jadwal. JavaScript mencari elemen ini lalu mengganti angkanya --}}
                    <p class="text-5xl font-extrabold mt-1 text-primary transition-all duration-300"
                        data-dilayani="{{ $jadwalSaya->id }}">
                        {{ $dilayaniSaya ?? '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @else
    {{-- # Tidak ada antrian aktif -> tampilkan pesan kosong + ajakan mendaftar --}}
    <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-white p-8 mb-8 text-center">
        <i class="fas fa-ticket text-3xl text-slate-300 mb-3"></i>
        <p class="font-semibold text-slate-700">Anda belum memiliki antrian aktif</p>
        <p class="text-sm text-slate-500 mb-4">Daftar ke poli untuk mendapatkan nomor antrian.</p>
        <a href="{{ route('pasien.daftar') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90
                  text-white text-sm font-semibold rounded-xl transition">
            <i class="fas fa-file-medical text-xs"></i> Daftar Poli
        </a>
    </div>
    @endif

    {{-- ================= TABEL JADWAL & ANTRIAN ================= --}}
    <h3 class="font-bold text-slate-700 mb-3">Jadwal Poliklinik & Antrian Saat Ini</h3>

    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Poli</th>
                            <th class="px-6 py-4">Dokter</th>
                            <th class="px-6 py-4">Hari</th>
                            <th class="px-6 py-4">Jam Periksa</th>
                            <th class="px-6 py-4 text-center">Sedang Dilayani</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700">
                        @forelse($jadwals as $i => $jadwal)
                        @php $milikSaya = $antrianAktif && $antrianAktif->id_jadwal === $jadwal->id; @endphp
                        <tr class="border-t border-slate-100 transition {{ $milikSaya ? 'bg-indigo-50' : 'hover:bg-slate-50' }}">
                            <td class="px-6 py-4 text-slate-500">{{ $i + 1 }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $jadwal->dokter->poli->nama_poli ?? '-' }}
                                @if($milikSaya)
                                <span class="ml-1 px-2 py-0.5 rounded-md bg-primary text-white text-[10px] font-bold">ANTRIAN ANDA</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $jadwal->dokter->nama ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $jadwal->hari }}</td>
                            <td class="px-6 py-4">{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</td>
                            <td class="px-6 py-4 text-center">
                                {{-- # Angka ini juga diperbarui live lewat atribut data-dilayani --}}
                                <span data-dilayani="{{ $jadwal->id }}"
                                    class="inline-block min-w-12 px-3 py-1 rounded-lg bg-slate-100 text-primary font-bold text-base transition-all duration-300">
                                    {{ $nomorDilayani[$jadwal->id] ?? '-' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <i class="fas fa-calendar-xmark text-3xl mb-3 block"></i>
                                Belum ada jadwal poliklinik
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // # Dijalankan setelah halaman & app.js (yang membuat window.Echo) selesai dimuat
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Echo) return;

            // ---------- 1) Indikator koneksi ke Reverb ----------
            const badge = document.getElementById('status-live');
            const tampilStatus = (online) => {
                badge.className = 'inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold ' +
                    (online ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500');
                badge.innerHTML = `<span class="w-2 h-2 rounded-full ${online ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'}"></span>` +
                    (online ? 'Live' : 'Offline');
            };
            const koneksi = window.Echo.connector.pusher.connection;
            koneksi.bind('state_change', (s) => tampilStatus(s.current === 'connected'));
            tampilStatus(koneksi.state === 'connected');

            // ---------- 2) Dengarkan event dari server ----------
            // # channel('antrian')   = nama channel publik di AntrianDiperbarui::broadcastOn()
            // # '.antrian.diperbarui' = nama dari broadcastAs() (diawali titik)
            window.Echo.channel('antrian').listen('.antrian.diperbarui', (e) => {

                // # Ganti SEMUA angka "sedang dilayani" milik jadwal tersebut (tabel + banner)
                document.querySelectorAll(`[data-dilayani="${e.id_jadwal}"]`).forEach((el) => {
                    el.textContent = e.nomor_dilayani ?? '-';

                    // efek kedip kuning singkat supaya perubahan terlihat
                    el.classList.add('bg-amber-200', 'scale-110');
                    setTimeout(() => el.classList.remove('bg-amber-200', 'scale-110'), 1200);
                });

                // # Perbarui pesan status di banner jika event untuk jadwal saya
                const banner = document.getElementById('banner-antrian');
                if (!banner || Number(banner.dataset.jadwal) !== e.id_jadwal) return;

                const nomorSaya = Number(banner.dataset.nomorSaya);
                const dilayani = e.nomor_dilayani;
                const pesan = document.getElementById('pesan-status');

                if (dilayani === nomorSaya) {
                    pesan.innerHTML = '<i class="fas fa-bell"></i> Giliran Anda! Silakan menuju ruang periksa.';
                } else if (dilayani !== null && dilayani < nomorSaya) {
                    pesan.innerHTML = `<i class="fas fa-hourglass-half"></i> ${nomorSaya - dilayani} antrian lagi sebelum giliran Anda`;
                } else {
                    pesan.innerHTML = '<i class="fas fa-circle-check"></i> Pemeriksaan Anda telah selesai.';
                }
            });
        });
    </script>
    @endpush

</x-layouts.app>

{{-- # Tombol Export Excel yang dipakai ulang di 5 halaman (dokter, pasien, obat,
     # jadwal periksa, riwayat pasien). Cukup kirim $url route export-nya.
     # Memakai link biasa (GET) karena hanya MENGUNDUH data, tidak mengubah data. --}}
<a href="{{ $url }}" class="inline-flex items-center gap-2 px-5 py-2.5
          bg-emerald-600 hover:bg-emerald-700
          text-white text-sm font-semibold
          rounded-xl transition">
    <i class="fas fa-file-excel text-sm"></i>
    Export Excel
</a>

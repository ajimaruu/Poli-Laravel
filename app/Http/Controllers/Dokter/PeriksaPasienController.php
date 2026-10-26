<?php

namespace App\Http\Controllers\Dokter;

use App\Events\AntrianDiperbarui;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create($id)
    {
        # Pastikan pendaftaran ini milik jadwal dokter yang login & belum diperiksa
        $daftar = $this->ambilDaftarMilikDokter($id);

        if ($daftar->periksa) {
            return redirect()
                ->route('periksa-pasien.index')
                ->with('message', 'Pasien ini sudah diperiksa.')
                ->with('type', 'error');
        }

        $obats = Obat::orderBy('nama_obat')->get();
        return view('dokter.periksa-pasien.create', compact('obats', 'id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_daftar_poli' => 'required|exists:daftar_poli,id',
            'obat_json' => 'required|json',
            'catatan' => 'nullable|string',
        ]);

        $daftar = $this->ambilDaftarMilikDokter($request->id_daftar_poli);

        # Ambil id obat dari JSON, buang duplikat, lalu cek ke database
        $obatIds = collect(json_decode($request->obat_json, true))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $hasil = DB::transaction(function () use ($daftar, $obatIds, $request) {

            # Cegah 1 pendaftaran diperiksa 2x (misal tombol Simpan diklik dua kali)
            if (Periksa::where('id_daftar_poli', $daftar->id)->lockForUpdate()->exists()) {
                return 'sudah';
            }

            # Ambil obat dari DATABASE (bukan harga dari form) & kunci barisnya untuk update stok
            $obats = Obat::whereIn('id', $obatIds)->lockForUpdate()->get();

            # Obat dengan stok habis tidak boleh diresepkan
            $habis = $obats->firstWhere('stok', '<=', 0);
            if ($habis) {
                return "Stok obat {$habis->nama_obat} habis.";
            }

            # BIAYA dihitung di SERVER: total harga obat + jasa dokter.
            # Sebelumnya biaya diambil dari input form, padahal input form bisa diubah user.
            $biaya = $obats->sum('harga') + Periksa::BIAYA_JASA_DOKTER;

            $periksa = Periksa::create([
                'id_daftar_poli' => $daftar->id,
                'tgl_periksa' => now(),
                'catatan' => $request->catatan,
                'biaya_periksa' => $biaya,
                'status_bayar' => Periksa::BELUM_BAYAR, # tagihan baru, menunggu pasien membayar
            ]);

            foreach ($obats as $obat) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $obat->id,
                ]);

                # Stok obat berkurang 1 setiap kali diresepkan
                $obat->decrement('stok');
            }

            return $periksa;
        });

        if ($hasil === 'sudah') {
            return redirect()
                ->route('periksa-pasien.index')
                ->with('message', 'Pasien ini sudah diperiksa.')
                ->with('type', 'error');
        }

        if (is_string($hasil)) {
            return back()->withInput()->with('message', $hasil)->with('type', 'error');
        }

        # REAL-TIME: pemeriksaan selesai -> nomor "sedang dilayani" maju ke pasien berikutnya.
        # Semua dashboard pasien yang terbuka langsung berubah tanpa reload.
        AntrianDiperbarui::kirim($daftar->id_jadwal);

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data periksa berhasil disimpan.');
    }

    # Helper: ambil pendaftaran HANYA jika jadwalnya milik dokter yang login.
    # Dokter lain yang mencoba membuka id ini akan mendapat 404.
    private function ambilDaftarMilikDokter($id): DaftarPoli
    {
        return DaftarPoli::with('periksa')
            ->whereHas('jadwalPeriksa', fn ($q) => $q->where('id_dokter', Auth::id()))
            ->findOrFail($id);
    }
}

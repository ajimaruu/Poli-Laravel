<?php

namespace App\Http\Controllers\Dokter;

use App\Exports\RiwayatPasienExport;
use App\Http\Controllers\Controller;
use App\Models\Periksa;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class RiwayatPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $riwayat = Periksa::with(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa'])
            ->whereHas('daftarPoli.jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->latest('tgl_periksa')
            ->get();

        return view('dokter.riwayat-pasien.index', compact('riwayat'));
    }

    public function show($id)
    {
        # whereHas memastikan dokter hanya bisa membuka riwayat pasien yang IA periksa
        $periksa = Periksa::with(['daftarPoli.pasien', 'detailPeriksas.obat'])
            ->whereHas('daftarPoli.jadwalPeriksa', fn ($q) => $q->where('id_dokter', Auth::id()))
            ->findOrFail($id);

        return view('dokter.riwayat-pasien.show', compact('periksa'));
    }

    # EXPORT EXCEL: riwayat pasien yang pernah diperiksa dokter yang sedang login
    public function export()
    {
        return Excel::download(new RiwayatPasienExport(Auth::id()), 'riwayat-pasien-' . now()->format('Y-m-d') . '.xlsx');
    }
}

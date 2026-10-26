<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ObatExport;
use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ObatController extends Controller
{
    public function index()
    {
        $obat = Obat::all();
        return view('admin.obat.index', compact('obat'));
    }

    public function create()
    {
        return view('admin.obat.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_obat' => 'required|string|max:100',
            'kemasan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0', # kolom baru
        ]);

        Obat::create([
            'nama_obat' => $request->nama_obat,
            'kemasan' => $request->kemasan,
            'harga' => $request->harga,
            'stok' => $request->stok,
        ]);

        return redirect()
            ->route('obat.index')
            ->with('message', 'Data Obat Berhasil di Tambah')
            ->with('type', 'success');
    }

    public function edit(Obat $obat)
    {
        return view('admin.obat.edit', compact('obat'));
    }

    public function update(Request $request, Obat $obat)
    {
        $request->validate([
            'nama_obat' => 'required|string|max:100',
            'kemasan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0', # kolom baru
        ]);

        $obat->update([
            'nama_obat' => $request->nama_obat,
            'kemasan' => $request->kemasan,
            'harga' => $request->harga,
            'stok' => $request->stok,
        ]);

        return redirect()
            ->route('obat.index')
            ->with('message', 'Data Obat Berhasil di Ubah')
            ->with('type', 'success');
    }

    public function destroy(Obat $obat)
    {
        // Obat yang sudah dipakai di detail_periksa tidak bisa dihapus (foreign key).
        if ($obat->detailPeriksas()->exists()) {
            return redirect()
                ->route('obat.index')
                ->with('message', 'Obat tidak dapat dihapus karena sudah digunakan pada data periksa')
                ->with('type', 'error');
        }

        $obat->delete();

        return redirect()
            ->route('obat.index')
            ->with('message', 'Data Obat Berhasil di Hapus')
            ->with('type', 'success');
    }

    # EXPORT EXCEL: data obat lengkap dengan stok dan harga.
    # Excel::download() membuat file .xlsx dari class ObatExport lalu
    # langsung mengirimnya ke browser sebagai file unduhan.
    public function export()
    {
        return Excel::download(new ObatExport, 'data-obat-' . now()->format('Y-m-d') . '.xlsx');
    }
}

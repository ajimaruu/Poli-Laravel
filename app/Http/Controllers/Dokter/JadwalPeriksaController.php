<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalPeriksaController extends Controller
{
    public function index()
    {
        $jadwal = JadwalPeriksa::where('id_dokter', Auth::user()->id)->get();
        return view('dokter.jadwal-periksa.index', compact('jadwal'));
    }

    public function create()
    {
        return view('dokter.jadwal-periksa.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        JadwalPeriksa::create([
            'id_dokter' => Auth::user()->id,
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'status' => false,
        ]);

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('message', 'Jadwal Periksa berhasil di Tambah')
            ->with('type', 'success');
    }

    public function edit(JadwalPeriksa $jadwalPeriksa)
    {
        abort_if($jadwalPeriksa->id_dokter !== Auth::user()->id, 403);

        return view('dokter.jadwal-periksa.edit', ['jadwal' => $jadwalPeriksa]);
    }

    public function update(Request $request, JadwalPeriksa $jadwalPeriksa)
    {
        abort_if($jadwalPeriksa->id_dokter !== Auth::user()->id, 403);

        $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        $jadwalPeriksa->update([
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
        ]);

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('message', 'Jadwal Periksa berhasil di Ubah')
            ->with('type', 'success');
    }

    public function destroy(JadwalPeriksa $jadwalPeriksa)
    {
        abort_if($jadwalPeriksa->id_dokter !== Auth::user()->id, 403);

        if ($jadwalPeriksa->daftarPolis()->exists()) {
            return redirect()
                ->route('jadwal-periksa.index')
                ->with('message', 'Jadwal tidak dapat dihapus karena sudah dipakai pendaftaran pasien')
                ->with('type', 'error');
        }

        $jadwalPeriksa->delete();

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('message', 'Jadwal Periksa berhasil dihapus')
            ->with('type', 'success');
    }

    public function aktifkan(JadwalPeriksa $jadwalPeriksa)
    {
    abort_if($jadwalPeriksa->id_dokter !== Auth::user()->id, 403);

    JadwalPeriksa::where('id_dokter', Auth::user()->id)->update(['status' => false]);

    $jadwalPeriksa->update(['status' => true]);

    return redirect()
        ->route('jadwal-periksa.index')
        ->with('message', 'Jadwal Periksa berhasil diaktifkan')
        ->with('type', 'success');
    }
}
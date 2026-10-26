<?php

namespace App\Http\Controllers\Dokter;

use App\Exports\JadwalPeriksaExport;
use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
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
        $this->rapikanJam($request);

        $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ], [
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
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

        # Dikirim dengan dua nama supaya view yang memakai $jadwal maupun $jadwalPeriksa sama-sama jalan
        return view('dokter.jadwal-periksa.edit', [
            'jadwalPeriksa' => $jadwalPeriksa,
            'jadwal' => $jadwalPeriksa,
        ]);
    }

    public function update(Request $request, JadwalPeriksa $jadwalPeriksa)
    {
        abort_if($jadwalPeriksa->id_dokter !== Auth::user()->id, 403);

        $this->rapikanJam($request);

        $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ], [
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
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
        ->with('message', 'Jadwal Periksa Berhasil diaktifkan')
        ->with('type', 'success');
    }

    # EXPORT EXCEL: jadwal periksa MILIK dokter yang login (Auth::id()).
    # Excel::download() membuat file .xlsx lalu langsung mengirimnya ke browser.
    public function export()
    {
        return Excel::download(new JadwalPeriksaExport(Auth::id()), 'jadwal-periksa-' . now()->format('Y-m-d') . '.xlsx');
    }

    # Form edit terisi jam dari database lengkap dengan detik ("06:30:00"), sedangkan
    # jam yang baru dipilih dikirim tanpa detik ("19:30"). Rule after:jam_mulai membaca
    # kedua jam dengan format H:i, sehingga "06:30:00" gagal dibaca dan jam yang benar
    # pun ditolak. Solusinya: potong semua jam menjadi HH:MM sebelum divalidasi.
    private function rapikanJam(Request $request): void
    {
        $request->merge([
            'jam_mulai' => $request->filled('jam_mulai') ? substr($request->jam_mulai, 0, 5) : null,
            'jam_selesai' => $request->filled('jam_selesai') ? substr($request->jam_selesai, 0, 5) : null,
        ]);
    }
}
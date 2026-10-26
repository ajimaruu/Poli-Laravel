<?php

namespace App\Exports;

use App\Models\JadwalPeriksa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JadwalPeriksaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $no = 0;

    # id dokter dikirim dari controller (Auth::id()), sehingga file Excel
    # HANYA berisi jadwal milik dokter yang sedang login.
    public function __construct(private int $idDokter)
    {
    }

    public function collection(): Collection
    {
        # Urutkan Senin -> Minggu sesuai urutan hari, bukan abjad
        $urutanHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return JadwalPeriksa::where('id_dokter', $this->idDokter)
            ->get()
            ->sortBy(fn ($j) => [array_search($j->hari, $urutanHari), $j->jam_mulai])
            ->values();
    }

    public function headings(): array
    {
        return ['No', 'Hari', 'Jam Mulai', 'Jam Selesai'];
    }

    public function map($jadwal): array
    {
        return [
            ++$this->no,
            $jadwal->hari,
            substr($jadwal->jam_mulai, 0, 5),
            substr($jadwal->jam_selesai, 0, 5),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
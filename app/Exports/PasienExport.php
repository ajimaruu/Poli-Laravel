<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

# Semua nilai disimpan sebagai teks (No. KTP & No. HP tidak berubah format)
class PasienExport extends StringValueBinder implements
    WithCustomValueBinder, FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $no = 0;

    public function collection(): Collection
    {
        return User::where('role', 'pasien')->orderBy('id')->get();
    }

    public function headings(): array
    {
        return ['No', 'No. RM', 'Nama Pasien', 'Email', 'No. KTP', 'No. HP', 'Alamat'];
    }

    public function map($pasien): array
    {
        return [
            ++$this->no,
            $pasien->no_rm ?? '-',
            $pasien->nama,
            $pasien->email,
            $pasien->no_ktp ?? '-',
            $pasien->no_hp ?? '-',
            $pasien->alamat ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

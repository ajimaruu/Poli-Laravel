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

# extends StringValueBinder + WithCustomValueBinder:
# semua nilai ditulis ke Excel sebagai TEKS. Tanpa ini, No. KTP 16 digit
# akan dianggap angka dan tampil rusak seperti 3.2E+15.
class DokterExport extends StringValueBinder implements
    WithCustomValueBinder, FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $no = 0;

    public function collection(): Collection
    {
        # with('poli') = eager loading, nama poli tidak di-query per baris
        return User::with('poli')
            ->where('role', 'dokter')
            ->orderBy('nama')
            ->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Dokter', 'Email', 'No. KTP', 'No. HP', 'Alamat', 'Poli'];
    }

    public function map($dokter): array
    {
        return [
            ++$this->no,
            $dokter->nama,
            $dokter->email,
            $dokter->no_ktp,
            $dokter->no_hp,
            $dokter->alamat,
            $dokter->poli->nama_poli ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

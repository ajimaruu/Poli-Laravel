<?php

namespace App\Exports;

use App\Models\Obat;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

# CLASS EXPORT memakai package maatwebsite/excel.
# Setiap "interface" (FromCollection, WithHeadings, ...) menambah kemampuan:
#   FromCollection -> sumber data baris Excel
#   WithHeadings   -> baris judul kolom (baris pertama)
#   WithMapping    -> mengatur isi tiap kolom untuk setiap baris
#   ShouldAutoSize -> lebar kolom menyesuaikan isi
#   WithStyles     -> styling, di sini judul kolom dibuat tebal
class ObatExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $no = 0; # penghitung nomor urut

    public function collection(): Collection
    {
        return Obat::orderBy('nama_obat')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Obat', 'Kemasan', 'Harga (Rp)', 'Stok'];
    }

    # Dipanggil SEKALI untuk setiap baris data ($obat = satu baris dari collection())
    public function map($obat): array
    {
        return [
            ++$this->no,
            $obat->nama_obat,
            $obat->kemasan,
            $obat->harga,   # angka murni supaya bisa dijumlah di Excel
            $obat->stok,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]]; # baris 1 = judul kolom
    }
}

<?php

namespace App\Exports;

use App\Models\Periksa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RiwayatPasienExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $no = 0;

    # Hanya riwayat pasien yang diperiksa oleh dokter ini
    public function __construct(private int $idDokter)
    {
    }

    public function collection(): Collection
    {
        # Eager loading pasien + obat sekaligus agar export tetap cepat walau datanya banyak
        return Periksa::with(['daftarPoli.pasien', 'detailPeriksas.obat'])
            ->whereHas('daftarPoli.jadwalPeriksa', fn ($q) => $q->where('id_dokter', $this->idDokter))
            ->latest('tgl_periksa')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No', 'Tanggal Periksa', 'No Antrian', 'Nama Pasien', 'Keluhan',
            'Catatan Dokter', 'Obat', 'Biaya (Rp)', 'Status Pembayaran',
        ];
    }

    public function map($periksa): array
    {
        return [
            ++$this->no,
            $periksa->tgl_periksa->format('d/m/Y H:i'),
            $periksa->daftarPoli->no_antrian,
            $periksa->daftarPoli->pasien->nama ?? '-',
            $periksa->daftarPoli->keluhan ?? '-',
            $periksa->catatan ?? '-',
            # gabungkan semua nama obat jadi satu sel, contoh: "Paracetamol, Amoxicillin"
            $periksa->detailPeriksas->map(fn ($d) => $d->obat->nama_obat ?? '-')->implode(', ') ?: '-',
            $periksa->biaya_periksa,
            $periksa->labelStatusBayar(),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periksa extends Model
{
    protected $table = 'periksa';

    # Konstanta status pembayaran, supaya tidak salah ketik string di banyak tempat
    public const BELUM_BAYAR = 'belum_bayar';
    public const MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const LUNAS = 'lunas';

    # Biaya jasa dokter yang selalu ditambahkan ke total biaya periksa
    public const BIAYA_JASA_DOKTER = 150000;

    protected $fillable = [
        'id_daftar_poli',
        'tgl_periksa',
        'catatan',
        'biaya_periksa',
        'status_bayar',
        'bukti_bayar',
        'tgl_upload_bukti',
        'tgl_verifikasi',
    ];

    # Cast: kolom tanggal otomatis jadi objek Carbon,
    # jadi di view bisa langsung ->format('d/m/Y')
    protected function casts(): array
    {
        return [
            'tgl_periksa' => 'datetime',
            'tgl_upload_bukti' => 'datetime',
            'tgl_verifikasi' => 'datetime',
        ];
    }

    public function daftarPoli()
    {
        return $this->belongsTo(DaftarPoli::class, 'id_daftar_poli');
    }

    public function detailPeriksas()
    {
        return $this->hasMany(DetailPeriksa::class, 'id_periksa');
    }

    # Label status yang enak dibaca, dipakai di tabel dan file Excel
    public function labelStatusBayar(): string
    {
        return match ($this->status_bayar) {
            self::LUNAS => 'Lunas',
            self::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            default => 'Belum Bayar',
        };
    }
}

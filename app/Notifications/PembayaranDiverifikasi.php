<?php

namespace App\Notifications;

use App\Models\Periksa;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

# NOTIFIKASI untuk PASIEN:
# dikirim ketika admin memverifikasi pembayarannya (status menjadi LUNAS).
class PembayaranDiverifikasi extends Notification
{
    use Queueable;

    public function __construct(public Periksa $periksa)
    {
    }

    # Disimpan ke tabel "notifications" (database channel)
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $poli = $this->periksa->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? 'poliklinik';

        return [
            'jenis' => 'pembayaran_diverifikasi',
            'judul' => 'Pembayaran lunas',
            'pesan' => 'Pembayaran Anda sebesar Rp '
                . number_format($this->periksa->biaya_periksa, 0, ',', '.')
                . " untuk pemeriksaan di {$poli} telah diverifikasi admin.",
            'ikon' => 'fa-circle-check',
            'id_periksa' => $this->periksa->id,
        ];
    }
}

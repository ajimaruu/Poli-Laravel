<?php

namespace App\Notifications;

use App\Models\Periksa;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

# NOTIFIKASI untuk DOKTER:
# dikirim ketika pasien yang ia periksa mengupload bukti pembayaran.
#
# Sengaja TIDAK memakai "implements ShouldQueue", jadi notifikasi langsung
# disimpan saat itu juga (tidak perlu menjalankan queue worker).
class PembayaranDiunggah extends Notification
{
    use Queueable;

    # Data pemeriksaan dikirim lewat constructor supaya bisa dipakai di toDatabase()
    public function __construct(public Periksa $periksa)
    {
    }

    # Channel 'database' -> notifikasi disimpan sebagai 1 baris di tabel "notifications"
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    # Isi notifikasi. Array ini disimpan ke kolom "data" (format JSON) di tabel notifications,
    # lalu dibaca kembali di view lewat $notif->data['judul'], $notif->data['pesan'], dst.
    public function toDatabase(object $notifiable): array
    {
        $pasien = $this->periksa->daftarPoli->pasien;

        return [
            'jenis' => 'pembayaran_diunggah',
            'judul' => 'Bukti pembayaran diunggah',
            'pesan' => "Pasien {$pasien->nama} mengunggah bukti pembayaran sebesar Rp "
                . number_format($this->periksa->biaya_periksa, 0, ',', '.')
                . ' (pemeriksaan ' . $this->periksa->tgl_periksa->format('d/m/Y') . ').',
            'ikon' => 'fa-file-invoice-dollar',
            'id_periksa' => $this->periksa->id,
        ];
    }
}

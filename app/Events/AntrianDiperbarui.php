<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

# EVENT REAL-TIME ANTRIAN
# Dikirim (broadcast) ke Laravel Reverb setiap kali antrian sebuah jadwal berubah:
#   - pasien baru mendaftar      (jumlah antrian bertambah)
#   - dokter selesai memeriksa   (nomor yang dilayani maju)
# Reverb lalu meneruskannya lewat WebSocket ke semua browser yang mendengarkan,
# dan Laravel Echo di browser mengubah angka di halaman TANPA reload.
#
# ShouldBroadcastNow (bukan ShouldBroadcast):
#   event langsung dikirim saat itu juga, tidak masuk antrian job.
#   Jadi tidak perlu menjalankan "php artisan queue:work" agar live update berjalan.
class AntrianDiperbarui implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    # Properti public otomatis ikut terkirim ke browser sebagai data event
    public function __construct(
        public int $idJadwal,
        public ?int $nomorDilayani,
        public int $jumlahMenunggu,
    ) {
    }

    # Channel PUBLIK bernama "antrian".
    # Publik karena isinya hanya angka antrian (tanpa data pribadi pasien),
    # jadi semua pasien boleh mendengarkan tanpa proses otorisasi.
    public function broadcastOn(): array
    {
        return [
            new Channel('antrian'),
        ];
    }

    # Nama event yang didengarkan di JavaScript: .listen('.antrian.diperbarui', ...)
    # (titik di depan artinya memakai nama custom ini, bukan nama class)
    public function broadcastAs(): string
    {
        return 'antrian.diperbarui';
    }

    # Data yang dikirim ke browser
    public function broadcastWith(): array
    {
        return [
            'id_jadwal' => $this->idJadwal,
            'nomor_dilayani' => $this->nomorDilayani,
            'jumlah_menunggu' => $this->jumlahMenunggu,
        ];
    }

    # Helper: hitung angka terbaru lalu broadcast. Dipanggil dari controller.
    # Contoh: AntrianDiperbarui::kirim($idJadwal);
    public static function kirim(int $idJadwal): void
    {
        $antrian = app(\App\Services\AntrianService::class);

        try {
            broadcast(new self(
                $idJadwal,
                $antrian->nomorDilayani($idJadwal),
                $antrian->jumlahMenunggu($idJadwal),
            ));
        } catch (\Throwable $e) {
            # Jika server Reverb belum dijalankan, aplikasi TIDAK ikut error.
            # Data tetap tersimpan; hanya live update yang tidak terkirim.
            report($e);
        }
    }
}

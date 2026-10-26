<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periksa', function (Blueprint $table) {
            # Satu pemeriksaan = satu tagihan. Karena relasinya 1:1,
            # data pembayaran cukup ditaruh di tabel periksa (tidak perlu tabel baru).
            #
            # Alur status:
            #   belum_bayar  -> pasien upload bukti ->  menunggu_verifikasi
            #   menunggu_verifikasi -> admin konfirmasi -> lunas
            $table->enum('status_bayar', ['belum_bayar', 'menunggu_verifikasi', 'lunas'])
                ->default('belum_bayar')
                ->after('biaya_periksa');

            # Path file foto bukti, disimpan di storage/app/public/bukti-bayar
            $table->string('bukti_bayar')->nullable()->after('status_bayar');

            # Kapan pasien upload dan kapan admin verifikasi
            $table->timestamp('tgl_upload_bukti')->nullable()->after('bukti_bayar');
            $table->timestamp('tgl_verifikasi')->nullable()->after('tgl_upload_bukti');
        });
    }

    public function down(): void
    {
        Schema::table('periksa', function (Blueprint $table) {
            $table->dropColumn(['status_bayar', 'bukti_bayar', 'tgl_upload_bukti', 'tgl_verifikasi']);
        });
    }
};

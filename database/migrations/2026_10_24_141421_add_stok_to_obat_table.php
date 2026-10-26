<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            # Kolom stok dibutuhkan untuk Export Excel data obat
            # (soal: "Data Obat termasuk kolom stok dan harga").
            # default(0) supaya data obat lama tetap valid setelah migrate.
            $table->integer('stok')->default(0)->after('harga');
        });
    }

    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->dropColumn('stok');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('jadwal_periksa', function (Blueprint $table) {
        $table->boolean('status')->default(false)->after('jam_selesai');
    });
}

public function down(): void
{
    Schema::table('jadwal_periksa', function (Blueprint $table) {
        $table->dropColumn('status');
    });
}
};

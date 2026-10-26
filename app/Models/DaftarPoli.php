<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DaftarPoli extends Model
{
    protected $table = 'daftar_poli';

    protected $fillable = [
        'id_pasien',
        'id_jadwal',
        'keluhan',
        'no_antrian',
    ];

    # Pasien disimpan di tabel users (role = pasien), jadi relasinya ke User
    public function pasien()
    {
        return $this->belongsTo(User::class, 'id_pasien');
    }

    public function jadwalPeriksa()
    {
        return $this->belongsTo(JadwalPeriksa::class, 'id_jadwal');
    }

    # Relasi lama (dipakai halaman Periksa Pasien), tetap dipertahankan
    public function periksas()
    {
        return $this->hasMany(Periksa::class, 'id_daftar_poli');
    }

    # Satu pendaftaran hanya diperiksa satu kali -> hasOne lebih tepat.
    # Dipakai di Riwayat Pendaftaran, Dashboard Pasien, dan Pembayaran.
    public function periksa()
    {
        return $this->hasOne(Periksa::class, 'id_daftar_poli');
    }

    # Query scope: pendaftaran yang BELUM diperiksa = antrian yang masih aktif.
    # Cara pakai: DaftarPoli::aktif()->where(...)
    public function scopeAktif(Builder $query): Builder
    {
        return $query->doesntHave('periksa');
    }
}

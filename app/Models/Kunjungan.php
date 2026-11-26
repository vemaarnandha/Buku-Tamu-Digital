<?php

namespace App\Models;

use App\Enums\KlasifikasiStatus;
use App\Enums\StatusKunjungan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kunjungan extends Model
{
    protected $table = 'kunjungan';

    use HasFactory;

    protected $fillable = [
        'kode_publik', 'tamu_id', 'unit_id', 'pegawai_id', 'keperluan',
        'kategori', 'ringkasan_ai', 'status', 'klasifikasi_status',
        'diubah_oleh', 'alasan_tidak_terlayani', 'catatan',
        'waktu_datang', 'waktu_dilayani', 'waktu_selesai',
        'notifikasi_terkirim_pada',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusKunjungan::class,
            'klasifikasi_status' => KlasifikasiStatus::class,
            'waktu_datang' => 'datetime',
            'waktu_dilayani' => 'datetime',
            'waktu_selesai' => 'datetime',
            'notifikasi_terkirim_pada' => 'datetime',
        ];
    }

    public function tamu(): BelongsTo
    {
        return $this->belongsTo(Tamu::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function diubahOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(Rating::class);
    }
}

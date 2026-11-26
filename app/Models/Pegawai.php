<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    use HasFactory;

    protected $fillable = [
        'user_id', 'unit_id', 'nama', 'jabatan', 'no_wa', 'email',
        'aktif', 'bisa_menerima_tamu', 'terakhir_ditugaskan_pada',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'bisa_menerima_tamu' => 'boolean',
            'terakhir_ditugaskan_pada' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function kunjungan(): HasMany
    {
        return $this->hasMany(Kunjungan::class);
    }
}

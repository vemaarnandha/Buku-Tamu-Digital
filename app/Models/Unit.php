<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = ['kode', 'nama', 'lokasi_ruang', 'deskripsi', 'aktif'];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function jamLayanan(): HasMany
    {
        return $this->hasMany(JamLayanan::class);
    }

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }

    public function faq(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function kunjungan(): HasMany
    {
        return $this->hasMany(Kunjungan::class);
    }
}

<?php

namespace App\Models;

use App\Enums\KategoriTamu;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tamu extends Model
{
    protected $table = 'tamu';

    use HasFactory;

    protected $fillable = ['nama', 'kategori', 'instansi', 'alamat', 'no_hp'];

    protected function casts(): array
    {
        return [
            'kategori' => KategoriTamu::class,
        ];
    }

    public function kunjungan(): HasMany
    {
        return $this->hasMany(Kunjungan::class);
    }
}

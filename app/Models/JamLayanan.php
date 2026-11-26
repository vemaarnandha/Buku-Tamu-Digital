<?php

namespace App\Models;

use App\Enums\Hari;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JamLayanan extends Model
{
    use HasFactory;

    protected $table = 'jam_layanan';

    protected $fillable = ['unit_id', 'hari', 'jam_mulai', 'jam_selesai', 'aktif'];

    protected function casts(): array
    {
        return [
            'hari' => Hari::class,
            'aktif' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}

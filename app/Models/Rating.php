<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $table = 'rating';

    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['kunjungan_id', 'nilai', 'komentar', 'created_at'];

    protected function casts(): array
    {
        return [
            'nilai' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }
}

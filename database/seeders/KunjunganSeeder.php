<?php

namespace Database\Seeders;

use App\Enums\KlasifikasiStatus;
use App\Enums\StatusKunjungan;
use App\Models\Kunjungan;
use App\Models\Tamu;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KunjunganSeeder extends Seeder
{
    public function run(): void
    {
        $unit = Unit::first();
        $tamu = Tamu::factory()->create();

        Kunjungan::create([
            'kode_publik' => Str::random(12),
            'tamu_id' => $tamu->id,
            'unit_id' => $unit?->id,
            'keperluan' => 'Ingin konsultasi data.',
            'kategori' => 'konsultasi_data',
            'ringkasan_ai' => 'Tamu ingin konsultasi data.',
            'status' => StatusKunjungan::Selesai,
            'klasifikasi_status' => KlasifikasiStatus::Otomatis,
            'waktu_datang' => now()->subHour(),
            'waktu_dilayani' => now()->subMinutes(40),
            'waktu_selesai' => now()->subMinutes(10),
        ]);
    }
}

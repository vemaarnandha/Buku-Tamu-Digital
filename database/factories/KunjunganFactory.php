<?php

namespace Database\Factories;

use App\Enums\KlasifikasiStatus;
use App\Enums\StatusKunjungan;
use App\Models\Kunjungan;
use App\Models\Tamu;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Kunjungan>
 */
class KunjunganFactory extends Factory
{
    protected $model = Kunjungan::class;

    public function definition(): array
    {
        return [
            'kode_publik' => Str::random(12),
            'tamu_id' => Tamu::factory(),
            'unit_id' => null,
            'pegawai_id' => null,
            'keperluan' => fake()->sentence(),
            'kategori' => null,
            'ringkasan_ai' => null,
            'status' => StatusKunjungan::Menunggu,
            'klasifikasi_status' => KlasifikasiStatus::Diproses,
            'diubah_oleh' => null,
            'alasan_tidak_terlayani' => null,
            'catatan' => null,
            'waktu_datang' => now(),
            'waktu_dilayani' => null,
            'waktu_selesai' => null,
            'notifikasi_terkirim_pada' => null,
        ];
    }
}

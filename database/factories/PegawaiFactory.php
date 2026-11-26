<?php

namespace Database\Factories;

use App\Models\Pegawai;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pegawai>
 */
class PegawaiFactory extends Factory
{
    protected $model = Pegawai::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'unit_id' => Unit::factory(),
            'nama' => fake()->name(),
            'jabatan' => fake()->jobTitle(),
            'no_wa' => '628'.fake()->numerify('#########'),
            'email' => fake()->safeEmail(),
            'aktif' => true,
            'bisa_menerima_tamu' => true,
            'terakhir_ditugaskan_pada' => null,
        ];
    }
}

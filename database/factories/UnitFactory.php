<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'kode' => fake()->unique()->slug(1),
            'nama' => fake()->company(),
            'lokasi_ruang' => 'Ruang '.fake()->numberBetween(1, 20),
            'deskripsi' => fake()->sentence(),
            'aktif' => true,
        ];
    }
}

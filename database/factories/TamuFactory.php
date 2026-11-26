<?php

namespace Database\Factories;

use App\Enums\KategoriTamu;
use App\Models\Tamu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tamu>
 */
class TamuFactory extends Factory
{
    protected $model = Tamu::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'kategori' => fake()->randomElement(KategoriTamu::cases()),
            'instansi' => fake()->company(),
            'alamat' => fake()->address(),
            'no_hp' => '628'.fake()->unique()->numerify('#########'),
        ];
    }
}

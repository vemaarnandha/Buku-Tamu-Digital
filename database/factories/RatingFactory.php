<?php

namespace Database\Factories;

use App\Models\Kunjungan;
use App\Models\Rating;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rating>
 */
class RatingFactory extends Factory
{
    protected $model = Rating::class;

    public function definition(): array
    {
        return [
            'kunjungan_id' => Kunjungan::factory(),
            'nilai' => fake()->numberBetween(1, 5),
            'komentar' => fake()->optional()->sentence(),
            'created_at' => now(),
        ];
    }
}

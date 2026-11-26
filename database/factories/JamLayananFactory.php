<?php

namespace Database\Factories;

use App\Enums\Hari;
use App\Models\JamLayanan;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JamLayanan>
 */
class JamLayananFactory extends Factory
{
    protected $model = JamLayanan::class;

    public function definition(): array
    {
        return [
            'unit_id' => Unit::factory(),
            'hari' => fake()->randomElement(Hari::cases()),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '16:00:00',
            'aktif' => true,
        ];
    }
}

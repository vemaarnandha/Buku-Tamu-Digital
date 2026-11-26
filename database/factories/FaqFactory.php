<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'unit_id' => null,
            'pertanyaan' => fake()->sentence().'?',
            'jawaban' => fake()->paragraph(),
            'aktif' => true,
        ];
    }
}

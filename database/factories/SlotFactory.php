<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Slot;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Slot>
 */
class SlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $capacity = $this->faker->numberBetween(1, 100); // случайная ёмкость от 1 до 100
        $remaining = $this->faker->numberBetween(0, $capacity); // доступный остаток не может быть больше ёмкости

        return [
            'capacity' => $capacity,
            'remaining' => $remaining,
        ];
    }
}

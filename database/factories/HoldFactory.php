<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Hold;
use App\Models\Slot;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hold>
 */
class HoldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Сначала создаем слот, чтобы гарантировать наличие места
        $slot = Slot::factory()->create();

        return [
            'slot_id' => $slot->id,
            'status' => self::randomStatus(),
            'expires_at' => now()->addMinutes(5),
        ];
    }

    /**
     * Возвращает случайный допустимый статус
     */
    private static function randomStatus(): string
    {
        return self::$factory->randomElement([
            Hold::STATUS_HELD,
            Hold::STATUS_CONFIRMED,
            Hold::STATUS_CANCELLED
        ]);
    }
}

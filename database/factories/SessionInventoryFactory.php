<?php

namespace Database\Factories;

use App\Models\SessionInventory;
use App\Models\TrainingArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionInventory>
 */
class SessionInventoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_area_id' => TrainingArea::factory(),
            'reservation_date' => now()->addDays(3)->toDateString(),
            'time_slot' => '11:00',
            'capacity' => 2,
            'booked_places' => 0,
        ];
    }

    public function full(): static
    {
        return $this->state(fn (array $attributes) => ['booked_places' => $attributes['capacity']]);
    }
}

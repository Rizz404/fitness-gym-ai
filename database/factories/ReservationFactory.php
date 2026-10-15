<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\Reservation;
use App\Models\TrainingArea;
use App\Modules\Reservation\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => Reservation::generateReference(),
            'gym_id' => Gym::factory(),
            'training_area_id' => fn (array $attributes) => TrainingArea::factory()->create(['gym_id' => $attributes['gym_id']])->id,
            'guest_name' => fake()->name(),
            'guest_phone' => '+62 811 1111 222',
            'contact_type' => 'phone',
            'reservation_date' => now()->addDays(3)->toDateString(),
            'time_slot' => '11:00',
            'participants' => 1,
            'price_total' => 0,
            'program_name' => 'Training program',
            'area_name' => 'Training studio',
            'duration_minutes' => 60,
            'status' => ReservationStatus::Pending->value,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => ReservationStatus::Confirmed->value]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => ReservationStatus::Cancelled->value]);
    }
}

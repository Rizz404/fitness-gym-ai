<?php

namespace Database\Factories;

use App\Models\FitnessProgram;
use App\Models\Gym;
use App\Models\TrainingArea;
use App\Modules\Training\Enums\AreaType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrainingArea>
 */
class TrainingAreaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'gym_id' => Gym::factory(),
            'fitness_program_id' => fn (array $attributes) => FitnessProgram::factory()->create(['gym_id' => $attributes['gym_id'], 'price' => 0, 'duration_minutes' => 60])->id,
            'session_capacity' => 12,
            'schedule_times' => ['11:00', '13:00', '15:00'],
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'area_type' => AreaType::Indoor->value,
            'min_participants' => 1,
            'max_participants' => 1,
            'features' => [],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function forParticipants(int $min, int $max): static
    {
        return $this->state(fn () => ['min_participants' => $min, 'max_participants' => $max]);
    }

    public function withFee(float $fee): static
    {
        return $this->afterCreating(fn (TrainingArea $area) => $area->fitnessProgram->update(['price' => $fee]));
    }
}

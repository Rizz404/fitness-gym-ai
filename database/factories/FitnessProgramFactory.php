<?php

namespace Database\Factories;

use App\Models\FitnessProgram;
use App\Models\Gym;
use App\Modules\Program\Enums\ProgramCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FitnessProgram>
 */
class FitnessProgramFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'gym_id' => Gym::factory(),
            'category' => fake()->randomElement(ProgramCategory::values()),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(15, 90) * 1000,
            'difficulty' => 'beginner',
            'duration_minutes' => 60,
            'tags' => [],
            'requirements' => [],
            'is_featured' => false,
            'is_sold_out' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function soldOut(): static
    {
        return $this->state(fn () => ['is_sold_out' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}

<?php

namespace Database\Factories;

use App\Models\TrainingArea;
use App\Models\TrainingImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingImage>
 */
class TrainingImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_area_id' => TrainingArea::factory(),
            'image_url' => 'images/training/indoor.svg',
            'alt_text' => fake()->sentence(3),
            'tags' => [],
            'sort_order' => 0,
        ];
    }
}

<?php

namespace Tests;

use App\Models\Gym;
use App\Models\TrainingArea;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @param array<string, mixed> $attributes */
    protected function createTrainingArea(Gym $gym, array $attributes): TrainingArea
    {
        $price = $attributes['session_price'] ?? null;
        unset($attributes['session_price']);
        $area = TrainingArea::factory()->create(['gym_id' => $gym->id, ...$attributes]);
        if ($price !== null) {
            $area->fitnessProgram->update(['price' => $price]);
        }

        return $area;
    }
}

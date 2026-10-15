<?php

namespace App\Modules\Assistant\Tools\Support;

use App\Models\FitnessProgram;
use App\Models\Gym;
use App\Models\TrainingArea;

/**
 * The compact facts about a program item or training area that both the model and
 * the guest's cards are given.
 */
final class CatalogSummaries
{
    /**
     * @return array<string, mixed>
     */
    public static function fitnessProgram(FitnessProgram $item, Gym $gym, string $locale): array
    {
        return [
            'fitness_program_slug' => $item->slug,
            'name' => $item->translatedName($locale),
            'category' => $item->category,
            'price' => (float) $item->price,
            'currency' => $gym->currency,
            'difficulty' => $item->difficulty,
            'tags' => $item->tags ?? [],
            'requirements' => $item->requirements ?? [],
            'is_sold_out' => $item->is_sold_out,
            'image_url' => $item->image_source,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function trainingArea(TrainingArea $area, Gym $gym, string $locale): array
    {
        return [
            'name' => $area->translatedName($locale),
            'area_type' => $area->area_type,
            'min_participants' => $area->min_participants,
            'max_participants' => $area->max_participants,
            'features' => $area->features ?? [],
            'session_price' => (float) $area->session_price,
            'joining_fee' => $area->joining_fee !== null ? (float) $area->joining_fee : null,
            'currency' => $gym->currency,
            'thumbnail_url' => $area->images->first()?->image_source,
        ];
    }

    public static function fitnessProgramBySlug(Gym $gym, string $slug): ?FitnessProgram
    {
        return $gym->fitnessPrograms()->where('is_active', true)->where('slug', $slug)->first();
    }

    public static function trainingAreaBySlug(Gym $gym, string $slug): ?TrainingArea
    {
        return $gym->trainingAreas()->where('is_active', true)->where('slug', $slug)->first();
    }
}

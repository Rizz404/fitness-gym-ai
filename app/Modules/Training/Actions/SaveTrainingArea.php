<?php

namespace App\Modules\Training\Actions;

use App\Models\Gym;
use App\Models\TrainingArea;
use Illuminate\Support\Str;

/**
 * Creates a training area or updates one, together with the photos added or
 * removed in the same form. A new area gets a slug from its name; an existing
 * one keeps its slug so guest links never break.
 */
class SaveTrainingArea
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<int>  $imageIdsToDelete
     * @param  list<array{url: string, alt?: ?string, tags?: ?string}>  $newImages
     */
    public function handle(Gym $gym, array $attributes, array $imageIdsToDelete, array $newImages, ?TrainingArea $trainingArea = null): TrainingArea
    {
        if ($trainingArea) {
            $trainingArea->update($attributes);
        } else {
            $trainingArea = $gym->trainingAreas()->create([...$attributes, 'slug' => $this->uniqueSlug($gym, $attributes['name'])]);
        }

        $this->syncImages($trainingArea, $imageIdsToDelete, $newImages);

        return $trainingArea;
    }

    private function uniqueSlug(Gym $gym, string $name): string
    {
        $base = Str::slug($name) ?: 'area';
        $slug = $base;
        $suffix = 1;

        while ($gym->trainingAreas()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  list<int>  $deleteIds
     * @param  list<array{url: string, alt?: ?string, tags?: ?string}>  $newImages
     */
    private function syncImages(TrainingArea $trainingArea, array $deleteIds, array $newImages): void
    {
        if ($deleteIds !== []) {
            $trainingArea->images()->whereIn('id', $deleteIds)->delete();
        }

        $sort = $trainingArea->images()->max('sort_order') + 1;

        foreach ($newImages as $row) {
            $trainingArea->images()->create([
                'image_url' => $row['url'],
                'alt_text' => $row['alt'] ?? $trainingArea->name,
                'tags' => collect(explode(',', $row['tags'] ?? ''))->map(fn ($t) => trim($t))->filter()->values()->all(),
                'sort_order' => $sort++,
            ]);
        }
    }
}

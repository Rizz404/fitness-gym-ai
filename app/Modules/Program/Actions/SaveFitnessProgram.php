<?php

namespace App\Modules\Program\Actions;

use App\Models\FitnessProgram;
use App\Models\Gym;
use Illuminate\Support\Str;

/**
 * Creates a program item or updates one. A new item gets a slug from its name; an
 * existing one keeps its slug so guest links never break.
 */
class SaveFitnessProgram
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Gym $gym, array $attributes, ?FitnessProgram $fitnessProgram = null): FitnessProgram
    {
        if ($fitnessProgram) {
            $fitnessProgram->update($attributes);

            return $fitnessProgram;
        }

        return $gym->fitnessPrograms()->create([...$attributes, 'slug' => $this->uniqueSlug($gym, $attributes['name'])]);
    }

    private function uniqueSlug(Gym $gym, string $name): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $suffix = 1;

        while ($gym->fitnessPrograms()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}

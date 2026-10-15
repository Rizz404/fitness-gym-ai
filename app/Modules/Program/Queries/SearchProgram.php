<?php

namespace App\Modules\Program\Queries;

use App\Models\FitnessProgram;
use App\Models\Gym;
use App\Support\SearchText;
use Illuminate\Support\Collection;

/**
 * The active program, narrowed by keyword, category, level need or budget.
 */
class SearchProgram
{
    /**
     * @return Collection<int, FitnessProgram>
     */
    public function handle(Gym $gym, string $query = '', ?string $category = null, ?string $level = null, ?float $maxPrice = null, int $limit = 6): Collection
    {
        $query = SearchText::normalize($query);

        return $gym->fitnessPrograms()
            ->where('is_active', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (FitnessProgram $item) => ! $level || $item->hasTag($level))
            ->filter(fn (FitnessProgram $item) => $maxPrice === null || (float) $item->price <= $maxPrice)
            ->filter(fn (FitnessProgram $item) => SearchText::matches(implode(' ', [
                $item->name,
                $item->description,
                $item->category,
                implode(' ', $item->tags ?? []),
                collect($item->translations ?? [])->flatten()->implode(' '),
            ]), $query))
            ->take($limit);
    }
}

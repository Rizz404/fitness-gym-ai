<?php

namespace App\Modules\Knowledge\Queries;

use App\Models\Gym;
use App\Models\GymKnowledgeItem;
use App\Support\SearchText;
use Illuminate\Support\Collection;

/**
 * The gym's approved knowledge base, searched by keyword. The only source the
 * AI Fitness Concierge may state gym facts from.
 */
class SearchKnowledge
{
    /**
     * @return Collection<int, GymKnowledgeItem>
     */
    public function handle(Gym $gym, string $query, ?string $category = null, int $limit = 5): Collection
    {
        $query = SearchText::normalize($query);

        return $gym->knowledgeItems()
            ->where('is_active', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->get()
            ->filter(fn (GymKnowledgeItem $item) => SearchText::matches(
                $item->title.' '.$item->body.' '.implode(' ', $item->tags ?? []),
                $query,
            ))
            ->take($limit);
    }
}

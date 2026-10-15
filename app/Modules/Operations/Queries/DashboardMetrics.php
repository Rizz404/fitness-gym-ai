<?php

namespace App\Modules\Operations\Queries;

use App\Models\Gym;
use App\Models\HandoverRequest;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * What the gym team sees first when they sign in: counts of what they manage
 * and the few things waiting for them.
 */
class DashboardMetrics
{
    /**
     * @return array{fitness_programs: int, training_areas: int, knowledge_items: int, pending_reservations: int, open_handovers: int}
     */
    public function counts(Gym $gym): array
    {
        return [
            'fitness_programs' => $gym->fitnessPrograms()->count(),
            'training_areas' => $gym->trainingAreas()->count(),
            'knowledge_items' => $gym->knowledgeItems()->count(),
            'pending_reservations' => $gym->reservations()->where('status', Reservation::STATUS_PENDING)->count(),
            'open_handovers' => $this->openHandovers($gym)->count(),
        ];
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function recentReservations(Gym $gym, int $limit = 5): Collection
    {
        return $gym->reservations()->latest()->take($limit)->with('trainingArea')->get();
    }

    /**
     * @return Collection<int, HandoverRequest>
     */
    public function latestOpenHandovers(Gym $gym, int $limit = 5): Collection
    {
        return $this->openHandovers($gym)->latest()->take($limit)->with('conversation')->get();
    }

    /**
     * @return Builder<HandoverRequest>
     */
    private function openHandovers(Gym $gym): Builder
    {
        return HandoverRequest::whereHas('conversation', fn ($q) => $q->where('gym_id', $gym->id))
            ->where('status', HandoverRequest::STATUS_OPEN);
    }
}

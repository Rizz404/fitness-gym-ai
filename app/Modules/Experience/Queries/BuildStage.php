<?php

namespace App\Modules\Experience\Queries;

use App\Models\Gym;
use App\Models\GymKnowledgeItem;
use App\Modules\Experience\Presenters\SceneNarrations;
use App\Modules\Experience\Presenters\StageBackdrop;
use App\Modules\Experience\Presenters\StageCopy;
use App\Modules\Experience\Presenters\StageNavigation;
use App\Modules\Reservation\Support\ReservationHandover;
use App\Modules\Reservation\Support\SessionAvailability;

/**
 * Everything the shared stage shell needs, for whichever scene the guest has
 * walked into.
 */
class BuildStage
{
    public function __construct(
        private readonly ReservationHandover $handover,
        private readonly SessionAvailability $availability,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(Gym $gym, string $locale, string $scene): array
    {
        $fitnessPrograms = $gym->fitnessPrograms()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->values();

        $trainingAreas = $gym->trainingAreas()
            ->where('is_active', true)
            ->with(['fitnessProgram', 'images' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->values();

        $facilities = $gym->knowledgeItems()->where('is_active', true)
            ->whereIn('category', [GymKnowledgeItem::CATEGORY_FACILITIES, GymKnowledgeItem::CATEGORY_EVENTS, GymKnowledgeItem::CATEGORY_LOCATION])
            ->orderBy('sort_order')->get()->values();

        $labels = StageCopy::labels($locale);

        return [
            'gym' => $gym,
            'fitnessPrograms' => $fitnessPrograms,
            'trainingAreas' => $trainingAreas,
            'locale' => $locale,
            'scene' => $scene,
            'backdrop' => StageBackdrop::for($scene),
            'navItems' => StageNavigation::for($gym, $locale, $labels),
            'supportedLocales' => config('gym.locales'),
            'labels' => $labels,
            'stageCopy' => StageCopy::copy($locale),
            'terms' => StageCopy::terms($locale),
            'wizard' => StageCopy::wizard($locale),
            'narration' => StageCopy::narration($locale),
            'programNarrations' => SceneNarrations::program($gym, $fitnessPrograms, $locale),
            'trainingNarrations' => SceneNarrations::training($gym, $trainingAreas, $locale),
            'staffLinks' => $this->handover->forStaff($gym, $locale),
            'today' => now($gym->timezone)->toDateString(),
            'maxAdvanceDays' => SessionAvailability::MAX_ADVANCE_DAYS,
            'slots' => collect($this->availability->slotsFor($gym))
                ->map(fn (string $slot) => ['value' => $slot, 'label' => SessionAvailability::slotRange($slot)])
                ->all(),
            'infoItems' => $gym->knowledgeItems()->where('is_active', true)
                ->whereIn('category', [GymKnowledgeItem::CATEGORY_GENERAL, GymKnowledgeItem::CATEGORY_POLICIES, GymKnowledgeItem::CATEGORY_FAQ])
                ->orderBy('sort_order')->get()->groupBy('category'),
            'facilities' => $facilities,
            'sceneNarrations' => SceneNarrations::scenes($gym, $facilities, $locale),
        ];
    }
}

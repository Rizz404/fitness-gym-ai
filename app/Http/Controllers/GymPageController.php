<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResolveGym;
use App\Models\FitnessProgram;
use App\Models\Gym;
use App\Models\GymKnowledgeItem;
use App\Models\TrainingArea;
use App\Modules\Experience\Presenters\StageBackdrop;
use App\Modules\Experience\Presenters\StageCopy;
use App\Modules\Experience\Queries\BuildStage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * The guest-facing stage: one page per scene, all built from the same shell
 * data. The gym and the language are resolved by the ResolveGym middleware.
 */
class GymPageController extends Controller
{
    public function __construct(private readonly BuildStage $stage) {}

    public function index(Request $request): View
    {
        $gyms = Gym::published()->orderBy('name')->get();

        $locale = in_array($request->query('lang'), config('gym.locales'), true)
            ? $request->query('lang')
            : ($gyms->first()?->default_locale ?? config('gym.default_locale'));

        return view('opening', [
            'gyms' => $gyms,
            'locale' => $locale,
            'supportedLocales' => config('gym.locales'),
            'opening' => StageCopy::opening($locale),
            'avatarImage' => StageBackdrop::avatar(),
            'backdrop' => StageBackdrop::for('home'),
        ]);
    }

    public function show(Request $request): View
    {
        return view('gym.show', $this->stageData($request, 'home'));
    }

    public function program(Request $request): View
    {
        return view('gym.program-index', $this->stageData($request, 'program'));
    }

    public function fitnessProgram(Request $request, string $gymSlug, string $itemSlug): View
    {
        $data = $this->stageData($request, 'program-item');
        $items = $data['fitnessPrograms'];
        $index = $items->search(fn (FitnessProgram $item) => $item->slug === $itemSlug);

        abort_if($index === false, 404);

        return view('gym.program-detail', [
            ...$data,
            'fitnessProgram' => $items[$index],
            'itemIndex' => $index,
            ...$this->neighbours($items, $index, 'Item'),
        ]);
    }

    public function training(Request $request): View
    {
        return view('gym.training-index', $this->stageData($request, 'training'));
    }

    public function trainingArea(Request $request, string $gymSlug, string $areaSlug): View
    {
        $data = $this->stageData($request, 'training-area');
        $areas = $data['trainingAreas'];
        $index = $areas->search(fn (TrainingArea $area) => $area->slug === $areaSlug);

        abort_if($index === false, 404);

        return view('gym.training-detail', [
            ...$data,
            'trainingArea' => $areas[$index],
            'areaIndex' => $index,
            ...$this->neighbours($areas, $index, 'Area'),
        ]);
    }

    public function facilities(Request $request): View
    {
        return view('gym.facilities-index', $this->stageData($request, 'facilities'));
    }

    public function facility(Request $request, string $gymSlug, int $facilityId): View
    {
        $data = $this->stageData($request, 'facility');
        $facilities = $data['facilities'];
        $index = $facilities->search(fn (GymKnowledgeItem $item) => $item->id === $facilityId);

        abort_if($index === false, 404);

        return view('gym.facility-detail', [
            ...$data,
            'facility' => $facilities[$index],
            'facilityIndex' => $index,
            ...$this->neighbours($facilities, $index, 'Facility'),
        ]);
    }

    public function info(Request $request): View
    {
        return view('gym.info-index', $this->stageData($request, 'info'));
    }

    public function staff(Request $request): View
    {
        return view('gym.staff-index', $this->stageData($request, 'staff'));
    }

    public function reservationScene(Request $request): View
    {
        return view('gym.reservation-index', [
            ...$this->stageData($request, 'reservation'),
            'preselectedArea' => (string) $request->query('area', ''),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function stageData(Request $request, string $scene): array
    {
        return $this->stage->handle(ResolveGym::gym($request), ResolveGym::locale($request), $scene);
    }

    /**
     * The previous and next entry around an index, wrapping at both ends.
     *
     * @param  Collection<int, mixed>  $items
     * @return array<string, mixed>
     */
    private function neighbours(Collection $items, int $index, string $suffix): array
    {
        return [
            'previous'.$suffix => $items[($index - 1 + $items->count()) % $items->count()],
            'next'.$suffix => $items[($index + 1) % $items->count()],
        ];
    }
}

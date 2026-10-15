<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentGym;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TrainingAreaRequest;
use App\Models\TrainingArea;
use App\Modules\Training\Actions\SaveTrainingArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TrainingAreaController extends Controller
{
    use ResolvesCurrentGym;

    public function index(Request $request): View
    {
        $gym = $this->currentGym($request);

        $trainingAreas = $gym->trainingAreas()->withCount('images')->orderBy('sort_order')->get();

        return view('admin.training-areas.index', compact('gym', 'trainingAreas'));
    }

    public function create(Request $request): View
    {
        $gym = $this->currentGym($request);

        return view('admin.training-areas.form', [
            'gym' => $gym,
            'programs' => $gym->fitnessPrograms()->where('is_active', true)->get(),
            'trainingArea' => new TrainingArea(['area_type' => TrainingArea::TYPE_INDOOR, 'min_participants' => 1, 'max_participants' => 1, 'session_capacity' => 12, 'schedule_times' => ['07:00', '17:00'], 'is_active' => true]),
        ]);
    }

    public function store(TrainingAreaRequest $request, SaveTrainingArea $save): RedirectResponse
    {
        $trainingArea = $save->handle(
            $this->currentGym($request),
            $request->trainingAreaAttributes(),
            $request->imageIdsToDelete(),
            $request->newImages(),
        );

        return redirect()->route('admin.training-areas.index')->with('status', "Training area \"{$trainingArea->name}\" created.");
    }

    public function edit(Request $request, TrainingArea $trainingArea): View
    {
        $gym = $this->currentGym($request);
        Gate::authorize('update', $trainingArea);

        $trainingArea->load('images');

        return view('admin.training-areas.form', ['gym' => $gym, 'trainingArea' => $trainingArea, 'programs' => $gym->fitnessPrograms()->where('is_active', true)->get()]);
    }

    public function update(TrainingAreaRequest $request, TrainingArea $trainingArea, SaveTrainingArea $save): RedirectResponse
    {
        $save->handle($this->currentGym($request), $request->trainingAreaAttributes(), $request->imageIdsToDelete(), $request->newImages(), $trainingArea);

        return redirect()->route('admin.training-areas.index')->with('status', "Training area \"{$trainingArea->name}\" updated.");
    }

    public function destroy(Request $request, TrainingArea $trainingArea): RedirectResponse
    {
        $this->currentGym($request);
        Gate::authorize('delete', $trainingArea);

        $trainingArea->delete();

        return redirect()->route('admin.training-areas.index')->with('status', 'Training area deleted.');
    }
}

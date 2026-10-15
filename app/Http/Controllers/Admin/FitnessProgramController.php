<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentGym;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FitnessProgramRequest;
use App\Models\FitnessProgram;
use App\Modules\Program\Actions\SaveFitnessProgram;
use App\Modules\Program\Enums\ProgramCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FitnessProgramController extends Controller
{
    use ResolvesCurrentGym;

    public function index(Request $request): View
    {
        $gym = $this->currentGym($request);

        $fitnessPrograms = $gym->fitnessPrograms()->orderBy('category')->orderBy('sort_order')->get();

        return view('admin.program-items.index', compact('gym', 'fitnessPrograms'));
    }

    public function create(Request $request): View
    {
        $gym = $this->currentGym($request);

        return view('admin.program-items.form', [
            'gym' => $gym,
            'fitnessProgram' => new FitnessProgram(['category' => FitnessProgram::CATEGORY_STRENGTH, 'is_active' => true]),
            'categories' => ProgramCategory::values(),
        ]);
    }

    public function store(FitnessProgramRequest $request, SaveFitnessProgram $save): RedirectResponse
    {
        $fitnessProgram = $save->handle($this->currentGym($request), $request->fitnessProgramAttributes());

        return redirect()->route('admin.program-items.index')->with('status', "Program item \"{$fitnessProgram->name}\" created.");
    }

    public function edit(Request $request, FitnessProgram $fitnessProgram): View
    {
        $gym = $this->currentGym($request);
        Gate::authorize('update', $fitnessProgram);

        return view('admin.program-items.form', [
            'gym' => $gym,
            'fitnessProgram' => $fitnessProgram,
            'categories' => ProgramCategory::values(),
        ]);
    }

    public function update(FitnessProgramRequest $request, FitnessProgram $fitnessProgram, SaveFitnessProgram $save): RedirectResponse
    {
        $save->handle($this->currentGym($request), $request->fitnessProgramAttributes(), $fitnessProgram);

        return redirect()->route('admin.program-items.index')->with('status', "Program item \"{$fitnessProgram->name}\" updated.");
    }

    public function destroy(Request $request, FitnessProgram $fitnessProgram): RedirectResponse
    {
        $this->currentGym($request);
        Gate::authorize('delete', $fitnessProgram);

        $fitnessProgram->delete();

        return redirect()->route('admin.program-items.index')->with('status', 'Program item deleted.');
    }
}

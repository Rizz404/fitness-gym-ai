<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentGym;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KnowledgeItemRequest;
use App\Models\GymKnowledgeItem;
use App\Modules\Knowledge\Enums\KnowledgeCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class KnowledgeItemController extends Controller
{
    use ResolvesCurrentGym;

    public function index(Request $request): View
    {
        $gym = $this->currentGym($request);

        $items = $gym->knowledgeItems()->orderBy('category')->orderBy('sort_order')->get();

        return view('admin.knowledge-items.index', compact('gym', 'items'));
    }

    public function create(Request $request): View
    {
        $gym = $this->currentGym($request);

        return view('admin.knowledge-items.form', [
            'gym' => $gym,
            'item' => new GymKnowledgeItem(['category' => GymKnowledgeItem::CATEGORY_GENERAL, 'is_active' => true]),
            'categories' => KnowledgeCategory::values(),
        ]);
    }

    public function store(KnowledgeItemRequest $request): RedirectResponse
    {
        $item = $this->currentGym($request)->knowledgeItems()->create($request->knowledgeItemAttributes());

        return redirect()->route('admin.knowledge-items.index')->with('status', "Knowledge item \"{$item->title}\" created.");
    }

    public function edit(Request $request, GymKnowledgeItem $knowledgeItem): View
    {
        $gym = $this->currentGym($request);
        Gate::authorize('update', $knowledgeItem);

        return view('admin.knowledge-items.form', [
            'gym' => $gym,
            'item' => $knowledgeItem,
            'categories' => KnowledgeCategory::values(),
        ]);
    }

    public function update(KnowledgeItemRequest $request, GymKnowledgeItem $knowledgeItem): RedirectResponse
    {
        $this->currentGym($request);

        $knowledgeItem->update($request->knowledgeItemAttributes());

        return redirect()->route('admin.knowledge-items.index')->with('status', "Knowledge item \"{$knowledgeItem->title}\" updated.");
    }

    public function destroy(Request $request, GymKnowledgeItem $knowledgeItem): RedirectResponse
    {
        $this->currentGym($request);
        Gate::authorize('delete', $knowledgeItem);

        $knowledgeItem->delete();

        return redirect()->route('admin.knowledge-items.index')->with('status', 'Knowledge item deleted.');
    }
}

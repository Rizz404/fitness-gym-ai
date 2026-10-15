@php($isEdit = $fitnessProgram->exists)

<x-layouts.admin :title="$isEdit ? 'Edit program item' : 'Add program item'">
    <form method="POST" action="{{ $isEdit ? route('admin.program-items.update', $fitnessProgram) : route('admin.program-items.store') }}" class="space-y-8">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <x-ui.card heading="Basics">
            <div class="mt-4 grid grid-cols-2 gap-4">
                <x-ui.select name="category" label="Category">
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(old('category', $fitnessProgram->category) === $category)>{{ \Illuminate\Support\Str::headline($category) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="price" type="number" label="Price" step="500" :value="old('price', $fitnessProgram->price)" required />
                <x-ui.input name="name" label="Name (Indonesian, default)" :value="old('name', $fitnessProgram->name)" required class="col-span-2" />
                <x-ui.textarea name="description" label="Description (Indonesian, default)" :value="old('description', $fitnessProgram->description)" class="col-span-2" />
                <x-ui.input name="translations[en][name]" label="Name (English)" :value="old('translations.en.name', $fitnessProgram->translations['en']['name'] ?? '')" />
                <x-ui.input name="translations[ja][name]" label="Name (Japanese)" :value="old('translations.ja.name', $fitnessProgram->translations['ja']['name'] ?? '')" />
                <x-ui.textarea name="translations[en][description]" label="Description (English)" :value="old('translations.en.description', $fitnessProgram->translations['en']['description'] ?? '')" />
                <x-ui.textarea name="translations[ja][description]" label="Description (Japanese)" :value="old('translations.ja.description', $fitnessProgram->translations['ja']['description'] ?? '')" />
            </div>
        </x-ui.card>

        <x-ui.card heading="Details">
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <x-ui.input name="difficulty" label="Difficulty" :value="old('difficulty', $fitnessProgram->difficulty)" placeholder="beginner, intermediate, all_levels" />
                <x-ui.input name="duration_minutes" type="number" label="Duration (minutes)" min="60" max="60" required :value="old('duration_minutes', $fitnessProgram->duration_minutes)" />
                <x-ui.input name="sort_order" type="number" label="Sort order" :value="old('sort_order', $fitnessProgram->sort_order)" />
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.input name="tags" label="Tags (comma-separated)" :value="old('tags', implode(', ', $fitnessProgram->tags ?? []))" placeholder="beginner, intermediate, low_impact" />
                <x-ui.input name="requirements" label="Requirements (comma-separated)" :value="old('requirements', implode(', ', $fitnessProgram->requirements ?? []))" placeholder="sports_shoes, water_bottle" />
                <x-ui.input name="image_url" label="Image path or URL" :value="old('image_url', $fitnessProgram->image_url)" placeholder="images/gym/interior.webp or https://…" class="sm:col-span-2" />
            </div>
            <div class="mt-4 flex flex-wrap gap-6">
                <x-ui.checkbox name="is_featured" label="Featured" :checked="old('is_featured', $fitnessProgram->is_featured)" />
                <x-ui.checkbox name="is_sold_out" label="Temporarily unavailable" :checked="old('is_sold_out', $fitnessProgram->is_sold_out)" />
                <x-ui.checkbox name="is_active" label="Published (visible on the website)" :checked="old('is_active', $fitnessProgram->is_active)" />
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.link-button :href="route('admin.program-items.index')" variant="secondary">Cancel</x-ui.link-button>
            <x-ui.button type="submit">Save program item</x-ui.button>
        </div>
    </form>
</x-layouts.admin>

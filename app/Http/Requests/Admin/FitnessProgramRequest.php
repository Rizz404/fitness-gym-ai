<?php

namespace App\Http\Requests\Admin;

use App\Modules\Program\Enums\ProgramCategory;
use App\Support\Text;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Create or update a program item. The route model binding and the policy decide
 * whose item it is; this decides what may be written.
 */
class FitnessProgramRequest extends FormRequest
{
    /**
     * A record of another gym is "not found" before anything is validated.
     */
    public function authorize(): bool
    {
        $record = $this->route('fitness_program');

        if ($record) {
            Gate::authorize('update', $record);
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', 'string', Rule::in(ProgramCategory::values())],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'translations.en.name' => ['nullable', 'string'],
            'translations.en.description' => ['nullable', 'string'],
            'translations.ja.name' => ['nullable', 'string'],
            'translations.ja.description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'tags' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'difficulty' => ['nullable', 'string', 'max:30'],
            'duration_minutes' => ['required', 'integer', 'in:60'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_sold_out' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * The columns to write, without the slug (which only a new item gets).
     *
     * @return array<string, mixed>
     */
    public function fitnessProgramAttributes(): array
    {
        $data = $this->validated();

        return [
            'category' => $data['category'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'translations' => [
                'en' => ['name' => $data['translations']['en']['name'] ?? null, 'description' => $data['translations']['en']['description'] ?? null],
                'ja' => ['name' => $data['translations']['ja']['name'] ?? null, 'description' => $data['translations']['ja']['description'] ?? null],
            ],
            'price' => $data['price'],
            'image_url' => $data['image_url'] ?? null,
            'tags' => Text::slugList($data['tags'] ?? null),
            'requirements' => Text::slugList($data['requirements'] ?? null),
            'difficulty' => filled($data['difficulty'] ?? null) ? Str::of($data['difficulty'])->trim()->slug('_')->toString() : null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'is_featured' => $this->boolean('is_featured'),
            'is_sold_out' => $this->boolean('is_sold_out'),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ];
    }
}

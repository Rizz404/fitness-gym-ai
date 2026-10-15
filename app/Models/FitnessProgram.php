<?php

namespace App\Models;

use App\Modules\Program\Enums\ProgramCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One fitness program on the gym's program. The price here is the only price the
 * AI Fitness Concierge may ever quote, and only after reading it through a tool call.
 */
#[Fillable([
    'gym_id',
    'category',
    'name',
    'slug',
    'description',
    'translations',
    'price',
    'image_url',
    'tags',
    'requirements',
    'difficulty',
    'duration_minutes',
    'is_featured',
    'is_sold_out',
    'is_active',
    'sort_order',
])]
class FitnessProgram extends Model
{
    use HasFactory, SoftDeletes;

    public const CATEGORY_STRENGTH = ProgramCategory::Strength->value;

    public const CATEGORY_CARDIO = ProgramCategory::Cardio->value;

    public const CATEGORY_MIND_BODY = ProgramCategory::MindBody->value;

    public const CATEGORY_PERSONAL_TRAINING = ProgramCategory::PersonalTraining->value;

    public const CATEGORY_FUNCTIONAL = ProgramCategory::Functional->value;

    public const CATEGORY_RECOVERY = ProgramCategory::Recovery->value;

    /**
     * Tags a guest can filter on when they have a level need.
     */
    public const LEVEL_TAGS = ['beginner', 'intermediate', 'advanced', 'low_impact', 'all_levels'];

    /**
     * @return list<string>
     */
    public static function categories(): array
    {
        return ProgramCategory::values();
    }

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'tags' => 'array',
            'requirements' => 'array',
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_sold_out' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * The picture to show: a full URL as stored, or a file under public/.
     */
    public function getImageSourceAttribute(): ?string
    {
        if (blank($this->image_url)) {
            return null;
        }

        return str_starts_with($this->image_url, 'http') ? $this->image_url : asset($this->image_url);
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? [], true);
    }

    public function translatedName(string $locale): string
    {
        $value = $this->translations[$locale]['name'] ?? null;

        return filled($value) ? $value : $this->name;
    }

    public function translatedDescription(string $locale): ?string
    {
        $value = $this->translations[$locale]['description'] ?? null;

        return filled($value) ? $value : $this->description;
    }
}

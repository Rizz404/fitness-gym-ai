<?php

namespace App\Models;

use App\Modules\Training\Enums\AreaType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'gym_id',
    'fitness_program_id',
    'session_capacity',
    'schedule_times',
    'name',
    'slug',
    'description',
    'translations',
    'area_type',
    'min_participants',
    'max_participants',
    'features',
    'joining_fee',
    'is_active',
    'sort_order',
])]
class TrainingArea extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_INDOOR = AreaType::Indoor->value;

    public const TYPE_OUTDOOR = AreaType::Outdoor->value;

    public const TYPE_FUNCTIONAL = AreaType::Functional->value;

    public const TYPE_PRIVATE = AreaType::Private->value;

    /**
     * @return list<string>
     */
    public static function areaTypes(): array
    {
        return AreaType::values();
    }

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'features' => 'array',
            'schedule_times' => 'array',
            'joining_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function fitnessProgram(): BelongsTo
    {
        return $this->belongsTo(FitnessProgram::class);
    }

    public function getSessionPriceAttribute(): float
    {
        return (float) ($this->fitnessProgram?->price ?? 0);
    }

    public function images(): HasMany
    {
        return $this->hasMany(TrainingImage::class)->orderBy('sort_order');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(SessionInventory::class);
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

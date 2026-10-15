<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The only source of truth for session availability. Stands in for a real
 * POS / reservation-system adapter for V1: the AI must reach this table (or
 * its future adapter) through a tool call, never answer from its own memory.
 */
#[Fillable([
    'training_area_id',
    'reservation_date',
    'time_slot',
    'capacity',
    'booked_places',
])]
class SessionInventory extends Model
{
    use HasFactory;

    protected $table = 'session_inventory';

    public function setReservationDateAttribute(string|\DateTimeInterface $value): void
    {
        $this->attributes['reservation_date'] = CarbonImmutable::parse($value)->toDateString();
    }

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
        ];
    }

    public function trainingArea(): BelongsTo
    {
        return $this->belongsTo(TrainingArea::class);
    }

    public function availablePlaces(): int
    {
        return max(0, $this->capacity - $this->booked_places);
    }

    public function isAvailable(): bool
    {
        return $this->availablePlaces() > 0;
    }
}

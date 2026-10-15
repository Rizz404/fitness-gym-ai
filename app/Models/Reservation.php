<?php

namespace App\Models;

use App\Modules\Reservation\Enums\Occasion;
use App\Modules\Reservation\Enums\ReservationStatus;
use App\Modules\Reservation\Support\SessionAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reference',
    'gym_id',
    'training_area_id',
    'conversation_id',
    'guest_name',
    'guest_email',
    'guest_phone',
    'contact_type',
    'reservation_date',
    'time_slot',
    'participants',
    'occasion',
    'price_total',
    'program_name',
    'area_name',
    'duration_minutes',
    'status',
    'notes',
])]
class Reservation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = ReservationStatus::Pending->value;

    public const STATUS_CONFIRMED = ReservationStatus::Confirmed->value;

    public const STATUS_CANCELLED = ReservationStatus::Cancelled->value;

    /**
     * Occasions a guest can mention so the team can prepare the table.
     */
    public const OCCASIONS = [
        Occasion::FirstSession->value,
        Occasion::GeneralFitness->value,
        Occasion::Strength->value,
        Occasion::Mobility->value,
        Occasion::Consistency->value,
    ];

    public function setReservationDateAttribute(string|\DateTimeInterface $value): void
    {
        $this->attributes['reservation_date'] = CarbonImmutable::parse($value)->toDateString();
    }

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'price_total' => 'decimal:2',
        ];
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function trainingArea(): BelongsTo
    {
        return $this->belongsTo(TrainingArea::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * "11:00–13:00" for a reservation that starts at 11:00.
     */
    public function getTimeRangeAttribute(): string
    {
        return SessionAvailability::slotRange($this->time_slot);
    }

    /**
     * A short, unambiguous, non-sequential code participants can quote to staff.
     */
    public static function generateReference(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $reference = 'GY-'.collect(range(1, 6))
                ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
                ->implode('');
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }
}

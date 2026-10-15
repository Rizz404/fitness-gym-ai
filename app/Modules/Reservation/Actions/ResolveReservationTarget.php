<?php

namespace App\Modules\Reservation\Actions;

use App\Models\Gym;
use App\Models\TrainingArea;
use App\Modules\Reservation\Support\SessionAvailability;
use App\Modules\Reservation\Validation\ReservationRules;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/**
 * Turns the guest's already well-formed request into the training area and
 * date it names, or tells them in their own language what does not fit.
 */
class ResolveReservationTarget
{
    public function __construct(private readonly SessionAvailability $availability) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: TrainingArea, 1: CarbonImmutable}
     *
     * @throws ValidationException
     */
    public function handle(Gym $gym, array $data, string $locale): array
    {
        $area = $gym->trainingAreas()->where('is_active', true)->where('slug', $data['training_area_slug'])->first();

        if (! $area) {
            throw ValidationException::withMessages(['training_area_slug' => ReservationRules::message('area_unknown', $locale)]);
        }

        $date = CarbonImmutable::parse($data['reservation_date'], $gym->timezone)->startOfDay();
        $today = CarbonImmutable::now($gym->timezone)->startOfDay();

        if ($today->diffInDays($date) > SessionAvailability::MAX_ADVANCE_DAYS) {
            throw ValidationException::withMessages([
                'reservation_date' => ReservationRules::message('date_far', $locale, ['max' => (string) SessionAvailability::MAX_ADVANCE_DAYS]),
            ]);
        }

        if (! in_array($data['time_slot'], $this->availability->slotsFor($gym), true)) {
            throw ValidationException::withMessages(['time_slot' => ReservationRules::message('slot_unknown', $locale)]);
        }

        if ($this->availability->isSlotInPast($gym, $date, $data['time_slot'])) {
            throw ValidationException::withMessages(['time_slot' => ReservationRules::message('slot_past', $locale)]);
        }

        if (! $this->availability->fitsParty($area, (int) $data['participants'])) {
            throw ValidationException::withMessages(['participants' => ReservationRules::message('capacity', $locale)]);
        }

        return [$area, $date];
    }
}

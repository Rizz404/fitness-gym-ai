<?php

namespace App\Modules\Reservation\Support;

use App\Models\Gym;
use App\Models\SessionInventory;
use App\Models\TrainingArea;
use Carbon\CarbonImmutable;

/**
 * The single place where session availability and slot quotes are computed.
 * Both the AI Fitness Concierge tools and the guest-facing reservation wizard go
 * through here, so they can never disagree.
 */
class SessionAvailability
{
    /**
     * How far ahead a table can be requested.
     */
    public const MAX_ADVANCE_DAYS = 60;

    /**
     * Every slot is a fixed 60-minute session.
     */
    public const SLOT_MINUTES = 60;

    /**
     * Whether the party size suits the training area.
     */
    public function fitsParty(TrainingArea $area, int $participants): bool
    {
        return $participants === 1;
    }

    /**
     * The slot start times the gym takes reservations for, earliest first.
     *
     * @return list<string>
     */
    public function slotsFor(Gym $gym): array
    {
        return SessionInventory::query()
            ->whereIn('training_area_id', $gym->trainingAreas()->where('is_active', true)->select('id'))
            ->distinct()
            ->orderBy('time_slot')
            ->pluck('time_slot')
            ->all();
    }

    /**
     * "11:00–13:00" for a slot that starts at 11:00.
     */
    public static function slotRange(string $slot): string
    {
        $start = CarbonImmutable::createFromFormat('H:i', $slot);

        return $start->format('H:i').'–'.$start->addMinutes(self::SLOT_MINUTES)->format('H:i');
    }

    /**
     * Whether the slot has already begun in the gym's own time zone.
     */
    public function isSlotInPast(Gym $gym, CarbonImmutable $date, string $slot): bool
    {
        $start = CarbonImmutable::parse($date->toDateString().' '.$slot, $gym->timezone);

        return $start->lessThanOrEqualTo(CarbonImmutable::now($gym->timezone));
    }

    /**
     * Checks one slot. Returns null if there is no free table in it, which is
     * the one place availability truth comes from, never the model.
     *
     * @return array{date: string, time_slot: string, time_range: string, available_places: int, fee: float, joining_fee: ?float}|null
     */
    public function quote(TrainingArea $area, CarbonImmutable $date, string $slot, bool $lockForUpdate = false): ?array
    {
        if (! $area->is_active || ! $area->fitnessProgram?->is_active || $area->fitnessProgram->is_sold_out || ! in_array($slot, $area->schedule_times ?? [], true)) {
            return null;
        }

        $query = SessionInventory::where('training_area_id', $area->id)
            ->whereDate('reservation_date', $date->toDateString())
            ->where('time_slot', $slot);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $inventory = $query->first();

        if (! $inventory || ! $inventory->isAvailable()) {
            return null;
        }

        return [
            'date' => $date->toDateString(),
            'time_slot' => $slot,
            'time_range' => self::slotRange($slot),
            'available_places' => $inventory->availablePlaces(),
            'fee' => (float) $area->session_price,
            'joining_fee' => $area->joining_fee !== null ? (float) $area->joining_fee : null,
        ];
    }

    /**
     * Every slot of the day that still has a free table and has not started.
     *
     * @return list<string>
     */
    public function availableSlots(TrainingArea $area, CarbonImmutable $date): array
    {
        $gym = $area->gym;

        return SessionInventory::where('training_area_id', $area->id)
            ->whereDate('reservation_date', $date->toDateString())
            ->orderBy('time_slot')
            ->get()
            ->filter(fn (SessionInventory $row) => $row->isAvailable() && in_array($row->time_slot, $area->schedule_times ?? [], true) && $area->fitnessProgram?->is_active && ! $area->fitnessProgram->is_sold_out && ! $this->isSlotInPast($gym, $date, $row->time_slot))
            ->pluck('time_slot')
            ->values()
            ->all();
    }
}

<?php

namespace App\Modules\Reservation\Actions;

use App\Models\Reservation;
use App\Models\SessionInventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeReservationStatus
{
    public function handle(Reservation $reservation, string $status): Reservation
    {
        return DB::transaction(function () use ($reservation, $status) {
            $locked = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);
            if ($locked->status === Reservation::STATUS_CANCELLED && $status !== Reservation::STATUS_CANCELLED) {
                throw ValidationException::withMessages(['status' => 'A cancelled booking cannot be reopened. Create a new request.']);
            }
            if ($status === Reservation::STATUS_CANCELLED && $locked->status !== Reservation::STATUS_CANCELLED) {
                $this->releaseTable($locked);
            }
            $locked->update(['status' => $status]);

            return $locked;
        });
    }

    private function releaseTable(Reservation $reservation): void
    {
        SessionInventory::where('training_area_id', $reservation->training_area_id)
            ->whereDate('reservation_date', $reservation->reservation_date->toDateString())
            ->where('time_slot', $reservation->time_slot)
            ->where('booked_places', '>', 0)
            ->decrement('booked_places');
    }
}

<?php

namespace App\Modules\Reservation\Actions;

use App\Models\Conversation;
use App\Models\Gym;
use App\Models\Reservation;
use App\Models\SessionInventory;
use App\Models\TrainingArea;
use App\Modules\Reservation\Support\SessionAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Creates a pending reservation request and holds one table for it. Both the
 * reservation wizard and the AI Fitness Concierge's reservation tool end up here.
 */
class CreateReservationRequest
{
    public function __construct(private readonly SessionAvailability $availability) {}

    /**
     * Returns null when no table is free in that slot any more.
     *
     * @param  array{reservation_date: CarbonImmutable, time_slot: string, participants: int, occasion?: ?string, guest_name: string, guest_email?: ?string, guest_phone?: ?string, contact_type?: ?string, notes?: ?string}  $data
     */
    public function handle(Gym $gym, TrainingArea $area, array $data, ?Conversation $conversation = null): ?Reservation
    {
        if ($area->gym_id !== $gym->id || (int) $data['participants'] !== 1) {
            return null;
        }

        return DB::transaction(function () use ($gym, $area, $data, $conversation) {
            $quote = $this->availability->quote($area, $data['reservation_date'], $data['time_slot'], lockForUpdate: true);

            if (! $quote) {
                return null;
            }

            $reservation = Reservation::create([
                'reference' => Reservation::generateReference(),
                'gym_id' => $gym->id,
                'training_area_id' => $area->id,
                'conversation_id' => $conversation?->id,
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'] ?? null,
                'guest_phone' => $data['guest_phone'] ?? null,
                'contact_type' => $data['contact_type'] ?? null,
                'reservation_date' => $data['reservation_date']->toDateString(),
                'time_slot' => $data['time_slot'],
                'participants' => $data['participants'],
                'occasion' => $data['occasion'] ?? null,
                'price_total' => $quote['fee'],
                'program_name' => $area->fitnessProgram->name,
                'area_name' => $area->name,
                'duration_minutes' => SessionAvailability::SLOT_MINUTES,
                'status' => Reservation::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
            ]);

            SessionInventory::where('training_area_id', $reservation->training_area_id)
                ->whereDate('reservation_date', $reservation->reservation_date->toDateString())
                ->where('time_slot', $reservation->time_slot)
                ->increment('booked_places');

            return $reservation;
        });
    }
}

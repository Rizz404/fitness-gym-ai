<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveGym;
use App\Http\Requests\Api\QuoteReservationRequest;
use App\Http\Requests\Api\StoreReservationRequest;
use App\Models\Conversation;
use App\Models\Gym;
use App\Models\TrainingArea;
use App\Modules\Reservation\Actions\CreateReservationRequest;
use App\Modules\Reservation\Actions\ResolveReservationTarget;
use App\Modules\Reservation\Queries\FindAlternatives;
use App\Modules\Reservation\Support\ReservationHandover;
use App\Modules\Reservation\Support\SessionAvailability;
use App\Modules\Reservation\Validation\ReservationRules;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class ReservationController extends Controller
{
    public function __construct(
        private readonly SessionAvailability $availability,
        private readonly ResolveReservationTarget $resolveTarget,
        private readonly FindAlternatives $findAlternatives,
    ) {}

    public function quote(QuoteReservationRequest $request): JsonResponse
    {
        $gym = ResolveGym::gym($request);
        $locale = ResolveGym::locale($request);

        $data = $request->validated();
        [$area, $date] = $this->resolveTarget->handle($gym, $data, $locale);

        $quote = $this->availability->quote($area, $date, $data['time_slot']);

        if (! $quote) {
            return $this->unavailable($gym, $area, $date, $data, $locale);
        }

        return response()->json([
            'available' => true,
            'date' => $quote['date'],
            'time_slot' => $quote['time_slot'],
            'time_range' => $quote['time_range'],
            'available_places' => $quote['available_places'],
            'fee' => $quote['fee'],
            'joining_fee' => $quote['joining_fee'],
            'currency' => $gym->currency,
        ]);
    }

    public function store(
        StoreReservationRequest $request,
        CreateReservationRequest $create,
        ReservationHandover $handover,
    ): JsonResponse {
        $gym = ResolveGym::gym($request);
        $locale = ResolveGym::locale($request);

        $data = $request->validated();
        $isEmail = $request->isEmailContact();

        [$area, $date] = $this->resolveTarget->handle($gym, $data, $locale);

        $conversation = isset($data['guest_token'])
            ? Conversation::where('gym_id', $gym->id)->where('guest_token', $data['guest_token'])->first()
            : null;

        $reservation = $create->handle($gym, $area, [
            'reservation_date' => $date,
            'time_slot' => $data['time_slot'],
            'participants' => (int) $data['participants'],
            'occasion' => $data['occasion'] ?? null,
            'guest_name' => $data['guest_name'],
            'guest_email' => $isEmail ? $data['contact_value'] : null,
            'guest_phone' => $isEmail ? null : $data['contact_value'],
            'contact_type' => $data['contact_type'],
            'notes' => $data['special_request'] ?? null,
        ], $conversation);

        if (! $reservation) {
            return $this->unavailable($gym, $area, $date, $data, $locale);
        }

        return response()->json([
            'reference' => $reservation->reference,
            'status' => $reservation->status,
            'fee' => (float) $reservation->price_total,
            'currency' => $gym->currency,
            'handover' => $handover->forReservation($gym, $reservation, $locale),
        ], 201);
    }

    /**
     * No table is free in that area and slot: say so, and offer what is.
     *
     * @param  array<string, mixed>  $data
     */
    private function unavailable(Gym $gym, TrainingArea $requested, CarbonImmutable $date, array $data, string $locale): JsonResponse
    {
        $message = ReservationRules::message('unavailable', $locale);

        return response()->json([
            'message' => $message,
            'errors' => ['training_area_slug' => [$message]],
            'alternatives' => $this->findAlternatives->handle($gym, $requested, $date, $data['time_slot'], (int) $data['participants'], $locale),
            'currency' => $gym->currency,
        ], 422);
    }
}

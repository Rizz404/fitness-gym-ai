<?php

namespace App\Modules\Reservation\Support;

use App\Models\Gym;
use App\Models\Reservation;

/**
 * Builds the WhatsApp / phone / email hand-over links that carry a
 * reservation summary (or a plain "talk to staff" request) to the gym team.
 */
class ReservationHandover
{
    /**
     * @return array{whatsapp_url: ?string, phone_url: ?string, email_url: ?string}
     */
    public function forReservation(Gym $gym, Reservation $reservation, string $locale): array
    {
        $message = $this->reservationMessage($reservation, $locale);

        return $this->links($gym, $message, $this->subject('reservation', $locale).' '.$reservation->reference);
    }

    /**
     * @return array{whatsapp_url: ?string, phone_url: ?string, email_url: ?string}
     */
    public function forStaff(Gym $gym, string $locale): array
    {
        return $this->links($gym, $this->subject('staff_message', $locale), $this->subject('staff', $locale));
    }

    /**
     * @return array{whatsapp_url: ?string, phone_url: ?string, email_url: ?string}
     */
    private function links(Gym $gym, string $message, string $subject): array
    {
        $whatsapp = preg_replace('/\D+/', '', (string) $gym->whatsapp);
        $phone = preg_replace('/[^\d+]/', '', (string) $gym->phone);

        return [
            'whatsapp_url' => $whatsapp !== '' ? 'https://wa.me/'.$whatsapp.'?text='.rawurlencode($message) : null,
            'phone_url' => $phone !== '' ? 'tel:'.$phone : null,
            'email_url' => filled($gym->email) ? 'mailto:'.$gym->email.'?subject='.rawurlencode($subject).'&body='.rawurlencode($message) : null,
        ];
    }

    private function reservationMessage(Reservation $reservation, string $locale): string
    {
        $reservation->loadMissing('trainingArea');

        $label = fn (string $key): string => trans('handover.message.'.$key, [], $locale);

        return implode("\n", [
            $label('intro'),
            '',
            $label('name').": {$reservation->guest_name}",
            $label('date').': '.$reservation->reservation_date->locale($locale)->translatedFormat('l, j F Y'),
            $label('time').': '.SessionAvailability::slotRange($reservation->time_slot),
            $label('participants').": {$reservation->participants}",
            $label('training').': '.$reservation->area_name.' / '.$reservation->program_name,
            $label('occasion').': '.($reservation->occasion ?: '-'),
            $label('special').': '.($reservation->notes ?: '-'),
            '',
            $label('reference').": {$reservation->reference}",
        ]);
    }

    private function subject(string $key, string $locale): string
    {
        return trans('handover.subject.'.$key, [], $locale);
    }
}

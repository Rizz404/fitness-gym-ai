<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReservationPolicy extends GymScopedPolicy
{
    public function update(User $user, Reservation $reservation): Response
    {
        return $this->ownsGym($user, $reservation->gym_id);
    }
}

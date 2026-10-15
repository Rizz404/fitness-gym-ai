<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Staff manage the records of their own gym and nothing else. A record that
 * belongs to another gym is answered with "not found", so its existence is
 * never revealed.
 */
abstract class GymScopedPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->currentGym() !== null;
    }

    public function create(User $user): bool
    {
        return $user->currentGym() !== null;
    }

    protected function ownsGym(User $user, ?int $gymId): Response
    {
        $gym = $user->currentGym();

        return $gym !== null && $gym->id === $gymId
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}

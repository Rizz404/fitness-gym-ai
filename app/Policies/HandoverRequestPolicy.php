<?php

namespace App\Policies;

use App\Models\HandoverRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HandoverRequestPolicy extends GymScopedPolicy
{
    public function view(User $user, HandoverRequest $handover): Response
    {
        return $this->ownsGym($user, $handover->conversation->gym_id);
    }

    public function update(User $user, HandoverRequest $handover): Response
    {
        return $this->ownsGym($user, $handover->conversation->gym_id);
    }
}

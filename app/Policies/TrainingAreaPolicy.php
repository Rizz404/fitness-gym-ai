<?php

namespace App\Policies;

use App\Models\TrainingArea;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TrainingAreaPolicy extends GymScopedPolicy
{
    public function update(User $user, TrainingArea $trainingArea): Response
    {
        return $this->ownsGym($user, $trainingArea->gym_id);
    }

    public function delete(User $user, TrainingArea $trainingArea): Response
    {
        return $this->ownsGym($user, $trainingArea->gym_id);
    }
}

<?php

namespace App\Policies;

use App\Models\FitnessProgram;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FitnessProgramPolicy extends GymScopedPolicy
{
    public function update(User $user, FitnessProgram $fitnessProgram): Response
    {
        return $this->ownsGym($user, $fitnessProgram->gym_id);
    }

    public function delete(User $user, FitnessProgram $fitnessProgram): Response
    {
        return $this->ownsGym($user, $fitnessProgram->gym_id);
    }
}

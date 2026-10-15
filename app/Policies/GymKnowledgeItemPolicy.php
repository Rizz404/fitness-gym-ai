<?php

namespace App\Policies;

use App\Models\GymKnowledgeItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GymKnowledgeItemPolicy extends GymScopedPolicy
{
    public function update(User $user, GymKnowledgeItem $knowledgeItem): Response
    {
        return $this->ownsGym($user, $knowledgeItem->gym_id);
    }

    public function delete(User $user, GymKnowledgeItem $knowledgeItem): Response
    {
        return $this->ownsGym($user, $knowledgeItem->gym_id);
    }
}

<?php

namespace App\Modules\Conversation\Actions;

use App\Models\Conversation;
use App\Models\Gym;
use Illuminate\Support\Str;

class StartConversation
{
    public function handle(Gym $gym, string $locale = 'id'): Conversation
    {
        return Conversation::create([
            'gym_id' => $gym->id,
            'guest_token' => (string) Str::uuid(),
            'locale' => $locale,
            'status' => Conversation::STATUS_ACTIVE,
            'last_message_at' => now(),
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Gym;
use App\Modules\Conversation\Enums\ConversationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gym_id' => Gym::factory(),
            'guest_token' => (string) Str::uuid(),
            'locale' => 'id',
            'status' => ConversationStatus::Active->value,
            'current_scene' => 'home',
            'last_message_at' => now(),
        ];
    }

    public function handedOver(): static
    {
        return $this->state(fn () => ['status' => ConversationStatus::HandedOver->value]);
    }
}

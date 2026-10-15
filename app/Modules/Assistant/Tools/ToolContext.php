<?php

namespace App\Modules\Assistant\Tools;

use App\Models\Conversation;
use App\Models\Gym;

/**
 * What a tool may act on: one gym, one conversation, in the guest's language.
 */
final readonly class ToolContext
{
    public function __construct(
        public Gym $gym,
        public Conversation $conversation,
        public string $locale,
    ) {}
}

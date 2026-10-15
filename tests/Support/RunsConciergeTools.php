<?php

namespace Tests\Support;

use App\Models\Conversation;
use App\Models\Gym;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolExecutor;

/**
 * Runs one AI Fitness Concierge tool the way the model would, against real gym data.
 */
trait RunsConciergeTools
{
    /**
     * @param  array<string, mixed>  $input
     * @return array{text: string, ui: ?array<string, mixed>}
     */
    protected function runTool(Gym $gym, Conversation $conversation, string $name, array $input): array
    {
        $result = app(ToolExecutor::class)->execute(new ToolContext($gym, $conversation, $conversation->locale), $name, $input);

        return ['text' => $result->text, 'ui' => $result->ui];
    }
}

<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\FitnessProgram;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Program\Queries\SearchProgram;

final class SearchProgramTool implements Tool
{
    public function __construct(private readonly SearchProgram $search) {}

    public function name(): string
    {
        return 'search_program';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Search the gym program by keyword, category, level need or budget. Returns matching fitness programs with their real price, requirements and whether they are sold out. Use this whenever a guest asks what is on the program, wants a recommendation, or describes a training goal, level or budget rather than naming one item.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'Keywords such as "strength", "yoga", "beginner" — optional'],
                        'category' => [
                            'type' => 'string',
                            'enum' => FitnessProgram::categories(),
                            'description' => 'Optional category filter',
                        ],
                        'level' => [
                            'type' => 'string',
                            'enum' => FitnessProgram::LEVEL_TAGS,
                            'description' => 'Optional level requirement',
                        ],
                        'max_price' => ['type' => 'number', 'description' => 'Optional budget per item, in the gym currency'],
                    ],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $items = $this->search->handle(
            $context->gym,
            (string) ($arguments['query'] ?? ''),
            $arguments['category'] ?? null,
            $arguments['level'] ?? null,
            isset($arguments['max_price']) ? (float) $arguments['max_price'] : null,
        );

        if ($items->isEmpty()) {
            return new ToolResult('No program item matches that request. Tell the guest honestly and suggest a nearby alternative from another search_program call, or call request_human_handover for custom requests.');
        }

        $results = $items->map(fn (FitnessProgram $item) => CatalogSummaries::fitnessProgram($item, $context->gym, $context->locale))->values()->all();

        return ToolResult::json($results, ['type' => 'program_results', 'items' => $results]);
    }
}

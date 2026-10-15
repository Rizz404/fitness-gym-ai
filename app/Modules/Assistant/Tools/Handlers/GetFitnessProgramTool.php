<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;

final class GetFitnessProgramTool implements Tool
{
    public function name(): string
    {
        return 'get_fitness_program';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Get the full details of one program item by its slug (from a previous search_program result): description, price, requirements, difficulty style and whether it is sold out. Use this when the guest asks about a specific fitness program.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'fitness_program_slug' => ['type' => 'string'],
                    ],
                    'required' => ['fitness_program_slug'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $item = CatalogSummaries::fitnessProgramBySlug($context->gym, (string) ($arguments['fitness_program_slug'] ?? ''));

        if (! $item) {
            return new ToolResult('Program item not found.');
        }

        $detail = [
            ...CatalogSummaries::fitnessProgram($item, $context->gym, $context->locale),
            'description' => $item->translatedDescription($context->locale),
            'duration_minutes' => $item->duration_minutes,
            'note' => 'Price and description come from the current gym catalog. Preparation requirements are not medical clearance; discuss individual needs with a trainer.',
        ];

        return ToolResult::json($detail, ['type' => 'program_detail', 'item' => $detail]);
    }
}

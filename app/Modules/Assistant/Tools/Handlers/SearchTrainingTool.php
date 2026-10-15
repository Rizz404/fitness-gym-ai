<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\TrainingArea;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Support\ReservationInput;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Reservation\Support\SessionAvailability;

final class SearchTrainingTool implements Tool
{
    public function __construct(
        private readonly SessionAvailability $availability,
        private readonly ReservationInput $input,
    ) {}

    public function name(): string
    {
        return 'search_training';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Find training areas that fit the guest\'s date and participant count, with real-time session availability already checked. Optionally narrow to one time slot. Use this whenever a guest describes what they want (date, time, participant count, indoor or outdoor) rather than naming one area.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'date' => ['type' => 'string', 'description' => 'Reservation date, YYYY-MM-DD'],
                        'participants' => ['type' => 'integer', 'minimum' => 1],
                        'time_slot' => ['type' => 'string', 'description' => 'Optional slot start time, HH:MM, one of the reservation slots'],
                        'area_type' => ['type' => 'string', 'enum' => TrainingArea::areaTypes(), 'description' => 'Optional preference'],
                    ],
                    'required' => ['date', 'participants'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $gym = $context->gym;
        $date = $this->input->parseDate($gym, (string) ($arguments['date'] ?? ''));

        if (! $date) {
            return new ToolResult('date must be a valid YYYY-MM-DD date.');
        }

        if ($error = $this->input->dateProblem($gym, $date)) {
            return new ToolResult($error);
        }

        $participants = (int) ($arguments['participants'] ?? 0);
        $slot = $arguments['time_slot'] ?? null;
        $areaType = $arguments['area_type'] ?? null;

        if ($slot && ! $this->input->isKnownSlot($gym, (string) $slot)) {
            return new ToolResult($this->input->unknownSlotMessage($gym));
        }

        $matches = [];

        foreach ($gym->trainingAreas()->where('is_active', true)->with('images')->orderBy('sort_order')->get() as $area) {
            if (! $this->availability->fitsParty($area, $participants) || ($areaType && $area->area_type !== $areaType)) {
                continue;
            }

            $slots = $this->availability->availableSlots($area, $date);

            if ($slot) {
                $slots = array_values(array_intersect($slots, [$slot]));
            }

            if ($slots === []) {
                continue;
            }

            $matches[] = [
                'training_area_slug' => $area->slug,
                ...CatalogSummaries::trainingArea($area, $gym, $context->locale),
                'date' => $date->toDateString(),
                'available_slots' => array_map(fn (string $s) => ['time_slot' => $s, 'time_range' => SessionAvailability::slotRange($s)], $slots),
            ];
        }

        if ($matches === []) {
            return new ToolResult('No training area has a free place for that date, participant count and time. Tell the guest honestly and offer a different time or date, or call request_human_handover for a large group.');
        }

        return ToolResult::json($matches, [
            'type' => 'training_results',
            'date' => $date->toDateString(),
            'participants' => $participants,
            'areas' => $matches,
        ]);
    }
}

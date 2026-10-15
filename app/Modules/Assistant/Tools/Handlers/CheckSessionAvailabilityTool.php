<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Support\ReservationInput;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Reservation\Support\SessionAvailability;

final class CheckSessionAvailabilityTool implements Tool
{
    public function __construct(
        private readonly SessionAvailability $availability,
        private readonly ReservationInput $input,
    ) {}

    public function name(): string
    {
        return 'check_session_availability';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Get the current, real-time availability and exact reservation fee for one training area on a specific date and time slot. ALWAYS call this before telling a guest a session has a free place or confirming a fee — never state availability from memory.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'training_area_slug' => ['type' => 'string'],
                        'date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                        'time_slot' => ['type' => 'string', 'description' => 'Slot start time, HH:MM'],
                        'participants' => ['type' => 'integer', 'minimum' => 1],
                    ],
                    'required' => ['training_area_slug', 'date', 'time_slot'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $gym = $context->gym;
        $area = CatalogSummaries::trainingAreaBySlug($gym, (string) ($arguments['training_area_slug'] ?? ''));

        if (! $area) {
            return new ToolResult('Training area not found.');
        }

        $date = $this->input->parseDate($gym, (string) ($arguments['date'] ?? ''));
        $slot = (string) ($arguments['time_slot'] ?? '');

        if (! $date) {
            return new ToolResult('date must be a valid YYYY-MM-DD date.');
        }

        if ($error = $this->input->dateProblem($gym, $date)) {
            return new ToolResult($error);
        }

        if (! $this->input->isKnownSlot($gym, $slot)) {
            return new ToolResult($this->input->unknownSlotMessage($gym));
        }

        $participants = isset($arguments['participants']) ? (int) $arguments['participants'] : null;

        if ($participants !== null && ! $this->availability->fitsParty($area, $participants)) {
            return new ToolResult("This participant count does not suit that training area (it seats {$area->min_participants}-{$area->max_participants} participants). Suggest another training area, or call request_human_handover for a large group.");
        }

        $quote = $this->availability->isSlotInPast($gym, $date, $slot)
            ? null
            : $this->availability->quote($area, $date, $slot);

        if (! $quote) {
            $alternatives = array_map(
                fn (string $s) => ['time_slot' => $s, 'time_range' => SessionAvailability::slotRange($s)],
                $this->availability->availableSlots($area, $date),
            );

            return ToolResult::json([
                'available' => false,
                'training_area_slug' => $area->slug,
                'message' => 'No free place in this training area for that slot.',
                'other_available_slots_that_day' => $alternatives,
            ], ['type' => 'availability', 'available' => false, 'training_area_slug' => $area->slug]);
        }

        $result = [
            'available' => true,
            'training_area_slug' => $area->slug,
            'name' => $area->translatedName($context->locale),
            'date' => $quote['date'],
            'time_slot' => $quote['time_slot'],
            'time_range' => $quote['time_range'],
            'available_places' => $quote['available_places'],
            'session_price' => $quote['fee'],
            'joining_fee' => $quote['joining_fee'],
            'currency' => $gym->currency,
        ];

        return ToolResult::json($result, ['type' => 'availability', 'available' => true, 'quote' => $result]);
    }
}

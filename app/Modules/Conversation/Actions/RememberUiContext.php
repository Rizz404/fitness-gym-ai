<?php

namespace App\Modules\Conversation\Actions;

use App\Models\Conversation;
use App\Models\Gym;

/**
 * Remembers where in the UI the guest is, so the concierge can answer for "this
 * drink" or the reservation they are filling in. Only values that exist for
 * this gym are kept; nothing personal is stored here.
 */
class RememberUiContext
{
    /**
     * @param  array{scene?: ?string, selected_fitness_program?: ?string, selected_training_area?: ?string, selected_facility?: ?int, reservation?: ?array<string, mixed>}  $context
     */
    public function handle(Gym $gym, Conversation $conversation, array $context): void
    {
        $updates = [];

        if (in_array($context['scene'] ?? null, Conversation::SCENES, true)) {
            $updates['current_scene'] = $context['scene'];
        }

        if (filled($context['selected_fitness_program'] ?? null)) {
            $fitnessProgram = $gym->fitnessPrograms()->where('is_active', true)->where('slug', $context['selected_fitness_program'])->first();

            if ($fitnessProgram) {
                $updates['selected_fitness_program_id'] = $fitnessProgram->id;
            }
        }

        if (filled($context['selected_training_area'] ?? null)) {
            $area = $gym->trainingAreas()->where('is_active', true)->where('slug', $context['selected_training_area'])->first();

            if ($area) {
                $updates['selected_training_area_id'] = $area->id;
            }
        }

        if (filled($context['selected_facility'] ?? null)) {
            $facility = $gym->knowledgeItems()->where('is_active', true)->whereKey($context['selected_facility'])->first();

            if ($facility) {
                $updates['selected_facility_id'] = $facility->id;
            }
        }

        if (array_key_exists('reservation', $context)) {
            $updates['reservation_state'] = $context['reservation'] ?: null;
        }

        if ($updates !== []) {
            $conversation->update($updates);
        }
    }
}

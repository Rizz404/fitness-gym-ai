<?php

namespace App\Http\Requests\Api;

use App\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * One guest message plus where in the UI the guest is when they send it.
 */
class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'guest_token' => ['required', 'uuid'],
            'message' => ['required', 'string', 'max:2000'],
            'scene' => ['nullable', Rule::in(Conversation::SCENES)],
            'selected_fitness_program' => ['nullable', 'string', 'max:120'],
            'selected_training_area' => ['nullable', 'string', 'max:120'],
            'selected_facility' => ['nullable', 'integer', 'min:1'],
            'reservation' => ['nullable', 'array'],
            'reservation.reservation_date' => ['nullable', 'date_format:Y-m-d'],
            'reservation.time_slot' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'reservation.participants' => ['nullable', 'integer', 'min:1', 'max:30'],
            'reservation.occasion' => ['nullable', 'string', 'max:30'],
            'reservation.training_area_slug' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * Where the guest is in the UI, as the concierge's conversation remembers it.
     *
     * @return array{scene?: ?string, selected_fitness_program?: ?string, selected_training_area?: ?string, selected_facility?: ?int, reservation?: array<string, mixed>}
     */
    public function uiContext(): array
    {
        $data = $this->validated();

        return [
            'scene' => $data['scene'] ?? null,
            'selected_fitness_program' => $data['selected_fitness_program'] ?? null,
            'selected_facility' => $data['selected_facility'] ?? null,
            'selected_training_area' => $data['selected_training_area'] ?? ($data['reservation']['training_area_slug'] ?? null),
            ...array_key_exists('reservation', $data)
                ? ['reservation' => array_filter($data['reservation'] ?? [], fn ($value) => $value !== null && $value !== '')]
                : [],
        ];
    }
}

<?php

namespace App\Models;

use App\Modules\Conversation\Enums\ConversationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'gym_id',
    'guest_token',
    'guest_name',
    'guest_email',
    'locale',
    'status',
    'handover_summary',
    'current_scene',
    'selected_fitness_program_id',
    'selected_training_area_id',
    'selected_facility_id',
    'reservation_state',
    'last_message_at',
])]
class Conversation extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = ConversationStatus::Active->value;

    public const STATUS_HANDED_OVER = ConversationStatus::HandedOver->value;

    public const STATUS_CLOSED = ConversationStatus::Closed->value;

    /**
     * The UI scenes the guest can be in while talking to the AI Fitness Concierge.
     */
    public const SCENES = ['home', 'program', 'fitness_program', 'training', 'training_area', 'facilities', 'facility_detail', 'reservation', 'handover'];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'reservation_state' => 'array',
        ];
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function selectedFitnessProgram(): BelongsTo
    {
        return $this->belongsTo(FitnessProgram::class, 'selected_fitness_program_id');
    }

    public function selectedTrainingArea(): BelongsTo
    {
        return $this->belongsTo(TrainingArea::class, 'selected_training_area_id');
    }

    public function selectedFacility(): BelongsTo
    {
        return $this->belongsTo(GymKnowledgeItem::class, 'selected_facility_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class)->orderBy('created_at');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function handoverRequest(): HasOne
    {
        return $this->hasOne(HandoverRequest::class)->latestOfMany();
    }

    public function isHandedOver(): bool
    {
        return $this->status === self::STATUS_HANDED_OVER;
    }
}

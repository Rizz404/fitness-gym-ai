<?php

namespace App\Modules\Program\Enums;

/**
 * The kinds of training program a gym program is grouped into.
 */
enum ProgramCategory: string
{
    case Strength = 'strength';
    case Cardio = 'cardio';
    case MindBody = 'mind_body';
    case PersonalTraining = 'personal_training';
    case Functional = 'functional';
    case Recovery = 'recovery';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

<?php

namespace App\Modules\Reservation\Enums;

/**
 * Occasions a guest can mention so the team can prepare the table.
 */
enum Occasion: string
{
    case FirstSession = 'first_session';
    case GeneralFitness = 'general_fitness';
    case Strength = 'strength';
    case Mobility = 'mobility';
    case Consistency = 'consistency';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

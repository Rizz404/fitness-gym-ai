<?php

namespace App\Modules\Training\Enums;

/**
 * Where in the gym a training area is.
 */
enum AreaType: string
{
    case Indoor = 'indoor';
    case Outdoor = 'outdoor';
    case Functional = 'functional';
    case Private = 'private';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

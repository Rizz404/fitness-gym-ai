<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gym experience
    |--------------------------------------------------------------------------
    |
    | Guest-facing settings shared by every gym. Per-gym values (name,
    | currency, opening hours, default locale) live in the gyms table.
    | Provider connection values stay in config/services.php under
    | "local_llm"; AI budgets are in config/assistant.php.
    |
    */

    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))))),

    'locales' => ['id', 'en', 'ja'],

    'default_locale' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Stage artwork
    |--------------------------------------------------------------------------
    |
    | The small round picture of the concierge used beside chat and narration,
    | and the artwork per scene: a plain illustrated background plus one
    | cut-out of the concierge layered in front, so she can stand clear of the
    | panels. Scenes without artwork of their own borrow another scene's.
    |
    */

    'avatar_image' => 'images/gym/concierge.webp',

    'scenes' => [
        'home' => ['image' => 'images/gym/interior.webp', 'character' => 'images/gym/concierge.webp', 'focus' => 'center 40%', 'focusMobile' => '50% 30%', 'anchor' => 'right', 'text' => 'top'],
        'program' => ['image' => 'images/gym/interior.webp', 'character' => 'images/gym/concierge.webp', 'focus' => 'center 40%', 'focusMobile' => '50% 30%', 'anchor' => 'right', 'text' => 'top'],
        'training' => ['image' => 'images/gym/interior.webp', 'character' => 'images/gym/concierge.webp', 'focus' => 'center 45%', 'focusMobile' => '60% 30%', 'anchor' => 'right', 'text' => 'top'],
        'facilities' => ['image' => 'images/gym/interior.webp', 'character' => 'images/gym/concierge.webp', 'focus' => 'center 45%', 'focusMobile' => '30% 30%', 'anchor' => 'right', 'text' => 'bottom'],
        'info' => ['image' => 'images/gym/interior.webp', 'character' => 'images/gym/concierge.webp', 'focus' => 'center 45%', 'focusMobile' => '70% 30%', 'anchor' => 'right', 'text' => 'bottom'],
        'staff' => ['image' => 'images/gym/interior.webp', 'character' => 'images/gym/concierge.webp', 'focus' => 'center 40%', 'focusMobile' => '60% 30%', 'anchor' => 'right', 'text' => 'top'],
        'reservation' => ['image' => 'images/gym/interior.webp', 'character' => 'images/gym/concierge.webp', 'focus' => 'center 45%', 'focusMobile' => '30% 30%', 'anchor' => 'right', 'text' => 'bottom'],
    ],

    'scene_artwork' => [
        'program-item' => 'program',
        'training-area' => 'training',
        'facility' => 'facilities',
    ],

];

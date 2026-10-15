<?php

namespace App\Modules\Experience\Presenters;

use App\Models\Gym;

/**
 * The main program of the stage. Sections that live on another page are marked
 * so the stage can fade out, as if the concierge were walking the guest over.
 */
final class StageNavigation
{
    /**
     * @param  array<string, string>  $labels
     * @return list<array{key: string, icon: string, label: string, chip: string, href: string, exit: bool, tour: ?string, topic: string}>
     */
    public static function for(Gym $gym, string $locale, array $labels): array
    {
        $tour = StageCopy::narration($locale);
        $url = fn (string $route) => route($route, ['gymSlug' => $gym->slug, 'lang' => $locale]);

        return [
            ['key' => 'program', 'icon' => 'dumbbell', 'label' => $labels['program_heading'], 'chip' => $labels['chip_program'], 'href' => $url('gym.program'), 'exit' => true, 'tour' => $tour['tour_program'], 'topic' => $labels['nav_program_q']],
            ['key' => 'training', 'icon' => 'studio', 'label' => $labels['training_heading'], 'chip' => $labels['chip_training'], 'href' => $url('gym.training'), 'exit' => true, 'tour' => $tour['tour_training'], 'topic' => $labels['nav_training_q']],
            ['key' => 'facilities', 'icon' => 'sparkle', 'label' => $labels['facilities_heading'], 'chip' => $labels['chip_facilities'], 'href' => $url('gym.facilities'), 'exit' => true, 'tour' => $tour['tour_facilities'], 'topic' => $labels['nav_facilities_q']],
            ['key' => 'info', 'icon' => 'info', 'label' => $labels['info_heading'], 'chip' => $labels['chip_info'], 'href' => $url('gym.info'), 'exit' => true, 'tour' => $tour['tour_info'], 'topic' => $labels['nav_info_q']],
            ['key' => 'reservation', 'icon' => 'calendar', 'label' => $labels['reservation_heading'], 'chip' => $labels['chip_reservation'], 'href' => $url('gym.reservation'), 'exit' => true, 'tour' => $tour['tour_reservation'], 'topic' => $labels['nav_reservation_q']],
            ['key' => 'staff', 'icon' => 'chat', 'label' => $labels['staff_heading'], 'chip' => $labels['chip_staff'], 'href' => $url('gym.staff'), 'exit' => true, 'tour' => $tour['tour_staff'], 'topic' => $labels['nav_staff_q']],
        ];
    }
}

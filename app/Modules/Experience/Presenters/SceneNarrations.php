<?php

namespace App\Modules\Experience\Presenters;

use App\Models\FitnessProgram;
use App\Models\Gym;
use App\Models\GymKnowledgeItem;
use App\Models\TrainingArea;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * What the AI Fitness Concierge says when the guest steps into a scene. Every sentence
 * is built from stored gym data, never generated.
 */
final class SceneNarrations
{
    /**
     * @param  Collection<int, FitnessProgram>  $items
     * @return array{program: ?string, item: array<string, string>}
     */
    public static function program(Gym $gym, Collection $items, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $terms = StageCopy::terms($locale);
        $money = self::money($gym);
        $sentence = self::sentence($locale);
        $separator = $locale === 'ja' ? '、' : ', ';

        if ($items->isEmpty()) {
            return ['program' => null, 'item' => []];
        }

        $intro = str_replace(
            [':count', ':price'],
            [(string) $items->count(), $money($items->min('price'))],
            $templates[$items->count() === 1 ? 'program_one' : 'program']
        );

        $narrations = $items->mapWithKeys(function (FitnessProgram $item) use ($templates, $terms, $money, $sentence, $separator, $locale) {
            $parts = [$item->translatedName($locale).'.', filled($item->translatedDescription($locale)) ? $sentence(trim($item->translatedDescription($locale))) : null];

            if ($item->difficulty) {
                $parts[] = str_replace(':difficulty', $terms['difficulty'][$item->difficulty] ?? Str::headline($item->difficulty), $templates['item_difficulty']);
            }

            if (! empty($item->requirements)) {
                $list = collect($item->requirements)->map(fn ($requirement) => $terms['requirement'][$requirement] ?? $requirement)->implode($separator);
                $parts[] = str_replace(':list', $list, $templates['item_requirements']);
            }

            $parts[] = str_replace(':price', $money($item->price), $templates['item_price']);

            if ($item->is_sold_out) {
                $parts[] = $templates['item_sold_out'];
            }

            return [$item->slug => implode(' ', array_filter($parts))];
        })->all();

        return ['program' => $intro, 'item' => $narrations];
    }

    /**
     * @param  Collection<int, TrainingArea>  $areas
     * @return array{training: ?string, area: array<string, string>}
     */
    public static function training(Gym $gym, Collection $areas, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $money = self::money($gym);
        $sentence = self::sentence($locale);

        if ($areas->isEmpty()) {
            return ['training' => null, 'area' => []];
        }

        $first = $areas->first()->translatedName($locale);
        $intro = str_replace(
            [':count', ':first'],
            [(string) $areas->count(), $first],
            $templates[$areas->count() === 1 ? 'training_one' : 'training']
        );

        $narrations = $areas->mapWithKeys(function (TrainingArea $area) use ($templates, $money, $sentence, $locale) {
            $parts = [$area->translatedName($locale).'.', filled($area->translatedDescription($locale)) ? $sentence(trim($area->translatedDescription($locale))) : null];

            $parts[] = $area->min_participants > 1
                ? str_replace([':min', ':max'], [(string) $area->min_participants, (string) $area->max_participants], $templates['seat_participants'])
                : str_replace(':max', (string) $area->max_participants, $templates['seat_participants_one']);

            $parts[] = (float) $area->session_price > 0
                ? str_replace(':fee', $money($area->session_price), $templates['seat_fee'])
                : $templates['seat_free'];

            if ($area->joining_fee !== null && (float) $area->joining_fee > 0) {
                $parts[] = str_replace(':spend', $money($area->joining_fee), $templates['seat_spend']);
            }

            return [$area->slug => implode(' ', array_filter($parts))];
        })->all();

        return ['training' => $intro, 'area' => $narrations];
    }

    /**
     * Intros for the facilities list and the gym information scene, built
     * only from stored gym data.
     *
     * @param  Collection<int, GymKnowledgeItem>  $facilities
     * @return array{facilities: ?string, info: string}
     */
    public static function scenes(Gym $gym, Collection $facilities, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $separator = $locale === 'ja' ? '、' : '; ';
        $time = fn (?string $value) => $value ? substr($value, 0, 5) : null;

        $intro = null;

        if ($facilities->isNotEmpty()) {
            $examples = $facilities->take(3)->map(fn ($item) => $item->translatedTitle($locale))->implode($separator);
            $intro = str_replace(
                [':count', ':examples'],
                [(string) $facilities->count(), $examples],
                $templates[$facilities->count() === 1 ? 'facilities_one' : 'facilities']
            );
        }

        $location = collect([$gym->city, $gym->country])->filter()->implode($locale === 'ja' ? '、' : ', ');
        $parts = [str_replace(':gym', $gym->name, $templates['info_welcome'])];

        if ($location !== '') {
            $parts[] = str_replace(':location', $location, $templates['info_place']);
        }

        if ($time($gym->opening_time) && $time($gym->closing_time)) {
            $parts[] = str_replace([':open', ':close'], [$time($gym->opening_time), $time($gym->closing_time)], $templates['info_hours']);
        }

        $parts[] = $templates['info_more'];

        return ['facilities' => $intro, 'info' => implode(' ', $parts)];
    }

    /**
     * @return \Closure(mixed): string
     */
    private static function money(Gym $gym): \Closure
    {
        return fn ($value) => $gym->currency.' '.number_format((float) $value, 0, ',', '.');
    }

    /**
     * @return \Closure(string): string
     */
    private static function sentence(string $locale): \Closure
    {
        return fn (string $text) => preg_match('/[.!?。！？]$/u', $text) ? $text : $text.($locale === 'ja' ? '。' : '.');
    }
}

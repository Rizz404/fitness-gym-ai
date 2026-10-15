<?php

namespace App\Http\Middleware;

use App\Models\Gym;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the published gym named by the {gymSlug} route segment and the
 * guest's language, so controllers never look either up themselves.
 *
 * The language is ?lang= on pages and "locale" in JSON bodies; an unsupported
 * value falls back to the gym's default language.
 */
class ResolveGym
{
    private const GYM = 'gym';

    private const LOCALE = 'locale';

    public function handle(Request $request, Closure $next): Response
    {
        $gym = Gym::query()
            ->published()
            ->where('slug', $request->route('gymSlug'))
            ->firstOrFail();

        $requested = $request->query('lang', $request->input('locale'));

        $locale = in_array($requested, config('gym.locales'), true) ? $requested : $gym->default_locale;

        $request->attributes->set(self::GYM, $gym);
        $request->attributes->set(self::LOCALE, $locale);

        app()->setLocale($locale);

        return $next($request);
    }

    public static function gym(Request $request): Gym
    {
        return $request->attributes->get(self::GYM);
    }

    public static function locale(Request $request): string
    {
        return $request->attributes->get(self::LOCALE);
    }
}

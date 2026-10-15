<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fitness Gym AI</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="stage-page antialiased" style="--concierge-image: url('{{ $avatarImage }}'); --stage-focus: {{ $backdrop['focus'] }}; --stage-focus-mobile: {{ $backdrop['focusMobile'] }}">
    <main class="stage" data-scene="opening">
        <div class="stage-loader" data-stage-loader role="status"><span class="gym-monogram" aria-hidden="true">F</span><p>{{ $opening['loading'] }}</p></div>
        <img src="{{ $backdrop['image'] }}" alt="" class="stage-image" fetchpriority="high">
        <div class="stage-shade"></div>
        <img src="{{ $backdrop['character'] }}" alt="" class="stage-character" data-anchor="right">

        <header class="stage-header">
            <span class="flex min-w-0 items-center gap-3">
                <span class="gym-monogram" aria-hidden="true">F</span>
                <span class="block truncate font-semibold tracking-tight">Fitness Gym AI</span>
            </span>
            <div class="stage-tools">
                <x-sound-toggle :on="$opening['sound_on']" :off="$opening['sound_off']" />
                <nav aria-label="Language" class="stage-lang">
                    @foreach ($supportedLocales as $code)
                        <a href="?lang={{ $code }}" lang="{{ $code }}" aria-label="{{ ['id' => 'Bahasa Indonesia', 'en' => 'English', 'ja' => '日本語'][$code] }}" @if($code === $locale) aria-current="true" @endif>{{ $code }}</a>
                    @endforeach
                </nav>
            </div>
        </header>

        <section class="opening-panel" aria-labelledby="opening-title">
            <p class="stage-eyebrow">MOVE WITH PURPOSE · AI FITNESS CONCIERGE</p>
            <h1 id="opening-title">{{ $opening['welcome'] }}<br><em>Fitness Gym AI</em></h1>
            <p class="opening-tagline">{{ $opening['tagline'] }}</p>

            @if ($gyms->count() === 1)
                @php
                    $gymSlug = $gyms->first()->slug;
                    $openingLinks = [
                        'program' => route('gym.program', ['gymSlug' => $gymSlug, 'lang' => $locale]),
                        'training' => route('gym.training', ['gymSlug' => $gymSlug, 'lang' => $locale]),
                        'reservation' => route('gym.reservation', ['gymSlug' => $gymSlug, 'lang' => $locale]),
                        'staff' => route('gym.staff', ['gymSlug' => $gymSlug, 'lang' => $locale]),
                    ];
                @endphp
                <ul class="opening-links">
                    @foreach ($openingLinks as $key => $href)
                        <li><a data-stage-exit href="{{ $href }}"><span>{{ $opening[$key] }}</span><span aria-hidden="true">›</span></a></li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="opening-enter">
            @forelse ($gyms as $gym)
                <a href="{{ route('gym.show', ['gymSlug' => $gym->slug, 'lang' => $locale]) }}" class="opening-button" data-stage-exit>
                    {{ str_replace(':name', $gym->name, $opening['enter']) }} <span aria-hidden="true">→</span>
                </a>
            @empty
                <p class="opening-empty">{{ $opening['empty'] }}</p>
            @endforelse
        </div>
    </main>
</body>
</html>

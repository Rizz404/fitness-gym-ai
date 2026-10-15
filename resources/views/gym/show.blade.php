<x-gym-stage :gym="$gym" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms">
    <x-slot:welcome>
        <section class="stage-welcome gym-dashboard">
            <p class="stage-eyebrow"><span class="live-dot"></span>{{ $stageCopy['dashboard'] }}</p>
            <h1>{{ $stageCopy['welcome'] }}<br><em>{{ $stageCopy['counter'] }}</em></h1>
            <p class="dashboard-intro">{{ $stageCopy['intro'] }}</p>
            <a href="{{ route('gym.program', ['gymSlug' => $gym->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_program'] }}" class="stage-action">{{ $stageCopy['start'] }} <span aria-hidden="true">→</span></a>
            <div class="dashboard-metrics">
                <div><strong>{{ $fitnessPrograms->count() }}</strong><span>{{ $stageCopy['program_count'] }}</span></div>
                <div><strong>60<span> min</span></strong><span>{{ $stageCopy['session_length'] }}</span></div>
                <div><strong>{{ substr($gym->opening_time, 0, 5) }}<span>—{{ substr($gym->closing_time, 0, 5) }}</span></strong><span>{{ $stageCopy['hours'] }}</span></div>
            </div>
            <div class="dashboard-next">
                <p class="stage-eyebrow">{{ $stageCopy['your_next'] }}</p>
                @foreach ($fitnessPrograms->take(2) as $item)
                    <a href="{{ route('gym.program-item', ['gymSlug' => $gym->slug, 'itemSlug' => $item->slug, 'lang' => $locale]) }}" data-stage-exit>
                        <span class="dashboard-item-icon"><x-stage-icon name="dumbbell" /></span>
                        <span><strong>{{ $item->translatedName($locale) }}</strong><small>{{ $terms['difficulty'][$item->difficulty] ?? $item->difficulty }} · {{ $gym->currency }} {{ number_format((float) $item->price, 0, ',', '.') }}</small></span><span aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </div>
        </section>
    </x-slot:welcome>
</x-gym-stage>

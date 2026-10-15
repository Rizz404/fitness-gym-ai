<x-gym-stage :gym="$gym" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$labels['program_heading'].' · '.$gym->name">
    <x-slot:welcome>
        <div class="stage-welcome stage-welcome-scene" data-anchor="{{ $backdrop['text'] }}">
            <span class="stage-eyebrow">{{ $stageCopy['explore'] }}</span>
            <h1>{{ $labels['program_heading'] }}</h1>
            @if ($programNarrations['program'])
                @include('gym.narrator', ['text' => $programNarrations['program'], 'key' => 'program', 'onStage' => true])
            @else
                <p>{{ $stageCopy['program_empty'] }}</p>
            @endif
        </div>
    </x-slot:welcome>

    @if ($fitnessPrograms->isNotEmpty())
        <x-slot:sidebar>@include('gym.program-nav')</x-slot:sidebar>
    @endif
</x-gym-stage>

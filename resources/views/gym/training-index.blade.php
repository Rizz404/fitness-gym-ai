<x-gym-stage :gym="$gym" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$labels['training_heading'].' · '.$gym->name">
    <x-slot:welcome>
        <div class="stage-welcome stage-welcome-scene" data-anchor="{{ $backdrop['text'] }}">
            <span class="stage-eyebrow">{{ $stageCopy['explore'] }}</span>
            <h1>{{ $labels['training_heading'] }}</h1>
            @if ($trainingNarrations['training'])
                @include('gym.narrator', ['text' => $trainingNarrations['training'], 'key' => 'training', 'onStage' => true])
            @else
                <p>{{ $stageCopy['training_empty'] }}</p>
            @endif
        </div>
    </x-slot:welcome>

    @if ($trainingAreas->isNotEmpty())
        <x-slot:sidebar>@include('gym.training-nav')</x-slot:sidebar>
    @endif
</x-gym-stage>

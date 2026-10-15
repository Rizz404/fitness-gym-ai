<x-gym-stage :gym="$gym" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$trainingArea->translatedName($locale).' · '.$gym->name">
    @if($trainingAreas->isNotEmpty())
        <x-slot:sidebar>@include('gym.training-nav')</x-slot:sidebar>
    @endif

    <div class="stage-panels">
        @include('gym.training-scene')
    </div>
</x-gym-stage>

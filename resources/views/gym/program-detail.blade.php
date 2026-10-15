<x-gym-stage :gym="$gym" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$fitnessProgram->translatedName($locale).' · '.$gym->name">
    @if($fitnessPrograms->isNotEmpty())
        <x-slot:sidebar>@include('gym.program-nav')</x-slot:sidebar>
    @endif

    <div class="stage-panels">
        @include('gym.program-scene')
    </div>
</x-gym-stage>

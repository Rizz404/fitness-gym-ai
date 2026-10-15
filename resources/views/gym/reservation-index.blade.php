<x-gym-stage :gym="$gym" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :nav="false" :title="$labels['reservation_heading'].' · '.$gym->name">
    <div class="stage-panels">
        @include('gym.reservation')
    </div>
</x-gym-stage>

{{-- On the training scenes the side card becomes the area index, so the guest can step between areas without going back. --}}
<aside class="stage-catalog" aria-label="{{ $labels['training_heading'] }}">
    <a href="{{ route('gym.show', ['gymSlug' => $gym->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" class="stage-program-back"><span aria-hidden="true">‹</span> {{ $stageCopy['explore'] }}</a>
    <p class="stage-eyebrow">{{ $labels['training_heading'] }}</p>
    <nav class="item-nav">
        @foreach ($trainingAreas as $item)
            <a href="{{ route('gym.training-area', ['gymSlug' => $gym->slug, 'areaSlug' => $item->slug, 'lang' => $locale]) }}"
                data-stage-exit
                @if (($trainingArea ?? null)?->slug === $item->slug) aria-current="page" @endif>
                @if ($item->images->first())
                    <img class="item-nav-thumb" src="{{ $item->images->first()->image_source }}" alt="" loading="lazy">
                @endif
                <span class="item-nav-text">
                    <span class="item-nav-name">{{ $item->translatedName($locale) }}</span>
                    <span class="item-nav-price">{{ $terms['area_type'][$item->area_type] ?? Str::headline($item->area_type) }} · {{ $item->session_capacity }} {{ $stageCopy['participants_unit'] }}</span>
                </span>
            </a>
        @endforeach
    </nav>
    <span class="stage-program-footer">{{ $stageCopy['available'] }} · FITNESS GYM AI</span>
</aside>

{{-- On the program scenes the side card becomes the program index, so the guest can step between dishes without going back. --}}
<aside class="stage-catalog" aria-label="{{ $labels['program_heading'] }}">
    <a href="{{ route('gym.show', ['gymSlug' => $gym->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" class="stage-program-back"><span aria-hidden="true">‹</span> {{ $stageCopy['explore'] }}</a>
    <p class="stage-eyebrow">{{ $labels['program_heading'] }}</p>
    <nav class="item-nav">
        @foreach ($fitnessPrograms->groupBy('category') as $category => $group)
            <div class="program-group">
            <p class="item-nav-heading">{{ $terms['category'][$category] ?? Str::headline($category) }}</p>
            @foreach ($group as $item)
                <a href="{{ route('gym.program-item', ['gymSlug' => $gym->slug, 'itemSlug' => $item->slug, 'lang' => $locale]) }}"
                    data-stage-exit
                    @if (($fitnessProgram ?? null)?->slug === $item->slug) aria-current="page" @endif>
                    @if ($item->image_source)
                        <img class="item-nav-thumb" src="{{ $item->image_source }}" alt="" loading="lazy">
                    @endif
                    <span class="item-nav-text">
                        <span class="item-nav-name">{{ $item->translatedName($locale) }}</span>
                        <span class="item-nav-price">{{ $gym->currency }} {{ number_format((float) $item->price, 0, ',', '.') }}@if ($item->is_sold_out)<span class="item-nav-badge">{{ $stageCopy['sold_out'] }}</span>@endif</span>
                    </span>
                </a>
            @endforeach
            </div>
        @endforeach
    </nav>
    <span class="stage-program-footer">{{ $stageCopy['available'] }} · FITNESS GYM AI</span>
</aside>

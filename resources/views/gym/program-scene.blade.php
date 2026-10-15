@php
    $itemName = $fitnessProgram->translatedName($locale);
    $itemUrl = fn ($item) => route('gym.program-item', ['gymSlug' => $gym->slug, 'itemSlug' => $item->slug, 'lang' => $locale]);
    $knownTags = collect($fitnessProgram->tags ?? [])->filter(fn ($tag) => isset($terms['tag'][$tag]));
@endphp
<section class="stage-content @container" aria-label="{{ $itemName }}">
    <a href="{{ route('gym.program', ['gymSlug' => $gym->slug, 'lang' => $locale]) }}" data-stage-exit class="panel-close" aria-label="{{ $labels['program_heading'] }}"><span aria-hidden="true">×</span></a>
    <article class="detail-scene">
        <div class="flex flex-wrap items-center justify-between gap-3 pr-12">
            <p class="stage-eyebrow">{{ $stageCopy['item_counter'] }} {{ $itemIndex + 1 }} / {{ $fitnessPrograms->count() }}</p>
            @if ($fitnessPrograms->count() > 1)
                <nav class="flex gap-2" aria-label="{{ $labels['program_heading'] }}">
                    <a href="{{ $itemUrl($previousItem) }}" data-stage-exit class="detail-scene-step" rel="prev"><span aria-hidden="true">←</span> {{ $stageCopy['prev_item'] }}</a>
                    <a href="{{ $itemUrl($nextItem) }}" data-stage-exit class="detail-scene-step" rel="next">{{ $stageCopy['next_item'] }} <span aria-hidden="true">→</span></a>
                </nav>
            @endif
        </div>

        @include('gym.narrator', ['text' => $programNarrations['item'][$fitnessProgram->slug], 'key' => 'item-'.$fitnessProgram->slug])

        <div class="mt-5 grid gap-7 @2xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
            <div class="detail-photo">
                @if ($fitnessProgram->image_source)
                    <img src="{{ $fitnessProgram->image_source }}" alt="{{ $itemName }}">
                @else
                    <div class="grid h-full place-items-center text-sm text-stone-500">{{ $stageCopy['no_photo'] }}</div>
                @endif
                @if ($fitnessProgram->is_sold_out)
                    <span class="detail-badge">{{ $stageCopy['sold_out'] }}</span>
                @endif
            </div>

            <div class="min-w-0">
                <h2 class="mt-0!">{{ $itemName }}</h2>
                <p class="mt-3 leading-relaxed text-stone-600">{{ $fitnessProgram->translatedDescription($locale) }}</p>

                <p class="detail-price mt-5">{{ $gym->currency }} {{ number_format((float) $fitnessProgram->price, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-stone-500">{{ $stageCopy['price_note'] }}</p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button type="button" class="stage-action" data-hero-quick-message="{{ str_replace(':name', $itemName, $stageCopy['ask_item_q']) }}">{{ $stageCopy['ask_item'] }} <span aria-hidden="true">↗</span></button>
                    <a href="{{ route('gym.reservation', ['gymSlug' => $gym->slug, 'lang' => $locale, 'area' => $trainingAreas->firstWhere('fitness_program_id', $fitnessProgram->id)?->slug]) }}" data-stage-exit data-tour-line="{{ $narration['tour_reservation'] }}" class="detail-scene-step">{{ $stageCopy['reserve_table'] }}</a>
                </div>

                <dl class="detail-scene-facts">
                    <div><dt>{{ $stageCopy['category'] }}</dt><dd>{{ $terms['category'][$fitnessProgram->category] ?? Str::headline($fitnessProgram->category) }}</dd></div>
                    @if ($fitnessProgram->difficulty)
                        <div><dt>{{ $stageCopy['difficulty'] }}</dt><dd>{{ Str::ucfirst($terms['difficulty'][$fitnessProgram->difficulty] ?? Str::headline($fitnessProgram->difficulty)) }}</dd></div>
                    @endif
                    @if ($fitnessProgram->duration_minutes)
                        <div><dt>{{ $stageCopy['duration_minutes'] }}</dt><dd>{{ $fitnessProgram->duration_minutes }} min</dd></div>
                    @endif
                </dl>

                @if ($knownTags->isNotEmpty())
                    <ul class="mt-5 flex flex-wrap gap-2">
                        @foreach ($knownTags as $tag)
                            <li class="tag-pill {{ in_array($tag, ['signature', 'bestseller'], true) ? 'is-accent' : '' }}">{{ $terms['tag'][$tag] }}</li>
                        @endforeach
                    </ul>
                @endif

                @if (! empty($fitnessProgram->requirements))
                    <h3 class="mt-6 text-sm font-semibold">{{ $stageCopy['contains'] }}</h3>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($fitnessProgram->requirements as $requirement)
                            <li class="tag-pill is-alert">{{ Str::ucfirst($terms['requirement'][$requirement] ?? $requirement) }}</li>
                        @endforeach
                    </ul>
                @endif
                <p class="detail-note">{{ $stageCopy['requirement_note'] }}</p>
            </div>
        </div>
    </article>
</section>

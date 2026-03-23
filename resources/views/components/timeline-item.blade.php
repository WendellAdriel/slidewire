<article {{ $attributes->merge(['data-status' => $status])->when($itemKey !== null, fn ($bag) => $bag->merge(['data-item-key' => (string) $itemKey]))->class([$wrapperClass()]) }}>
    <span class="{{ $markerClass() }}"></span>

    <div class="min-w-0 space-y-2">
        @if($label !== null)
            <p class="{{ $ui->overline($theme, true) }}">{{ $label }}</p>
        @endif

        <h3 class="{{ $ui->tokens($theme)['heading_text'] }} text-xl font-semibold text-balance sm:text-lg">{{ $title }}</h3>

        @if($description !== null)
            <p class="{{ $ui->body($theme) }} max-w-none">{{ $description }}</p>
        @endif
    </div>
</article>

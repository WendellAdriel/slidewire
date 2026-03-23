<article {{ $attributes->merge(['data-style' => $style, 'data-step-number' => $number ?? 'auto'])->class([$wrapperClass()]) }}>
    <span class="{{ $badgeClass() }} {{ $number === null ? 'before:[counter-increment:step] before:content-[counter(step)] before:text-current' : '' }}">
        @if($number !== null)
            {{ $number }}
        @endif
    </span>

    <div class="min-w-0 space-y-2">
        <h3 class="{{ $ui->tokens($theme)['heading_text'] }} text-xl font-semibold text-balance sm:text-lg">{{ $title }}</h3>

        @if($description !== null)
            <p class="{{ $ui->body($theme) }} max-w-none">{{ $description }}</p>
        @endif
    </div>
</article>

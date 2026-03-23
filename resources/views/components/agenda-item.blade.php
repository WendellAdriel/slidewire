<article {{ $attributes->merge(['data-active' => $active ? 'true' : 'false', 'data-style' => $style])->class([$wrapperClass()]) }}>
    @if($index !== null)
        <span class="{{ $indexClass() }}">{{ $index }}</span>
    @endif

    <div class="min-w-0 space-y-2">
        <h3 class="{{ $ui->tokens($theme)['heading_text'] }} text-xl font-semibold text-balance sm:text-lg">{{ $title }}</h3>

        @if($description !== null)
            <p class="{{ $ui->body($theme) }} max-w-none">{{ $description }}</p>
        @endif
    </div>
</article>

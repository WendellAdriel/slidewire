<section {{ $attributes->merge(['data-style' => $style])->when($highlight !== null, fn ($bag) => $bag->merge(['data-highlight' => (string) $highlight]))->class([$wrapperClass()]) }}>
    <div class="space-y-3">
        <h2 class="{{ $ui->heading($theme) }} text-4xl sm:text-3xl">{{ $title }}</h2>

        @if($subtitle !== null)
            <p class="{{ $ui->body($theme) }}">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="{{ $listClass() }}">{{ $slot }}</div>
</section>

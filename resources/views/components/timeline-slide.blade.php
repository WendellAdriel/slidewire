<section {{ $attributes->merge(['data-orientation' => $orientation])->when($highlight !== null, fn ($bag) => $bag->merge(['data-highlight' => (string) $highlight]))->class([$wrapperClass()]) }}>
    @if($title !== null)
        <div class="space-y-3">
            <h2 class="{{ $ui->heading($theme) }} text-3xl sm:text-2xl">{{ $title }}</h2>
        </div>
    @endif

    <div class="{{ $listClass() }}">{{ $slot }}</div>
</section>

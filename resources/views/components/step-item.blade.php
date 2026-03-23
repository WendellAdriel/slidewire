<div {{ $attributes->merge(['data-style' => $style, 'data-step-number' => $number ?? 'auto'])->class([$wrapperClass()]) }}>
    <span class="{{ $badgeClass() }} {{ $number === null ? 'before:[counter-increment:step] before:content-[counter(step)] before:text-current' : '' }}">
        @if($number !== null)
            {{ $number }}
        @endif
    </span>

    <div class="{{ $contentClass() }}">
        <h3 class="{{ $titleClass() }}">{{ $title }}</h3>

        @if($description !== null)
            <p class="{{ $descriptionClass() }}">{{ $description }}</p>
        @endif
    </div>
</div>

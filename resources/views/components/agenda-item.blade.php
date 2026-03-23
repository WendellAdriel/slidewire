<div {{ $attributes->merge(['data-active' => $active ? 'true' : 'false', 'data-style' => $style])->class([$wrapperClass()]) }}>
    @if($index !== null)
        <span class="{{ $indexClass() }}">{{ $index }}</span>
    @endif

    <div class="{{ $contentClass() }}">
        <h3 class="{{ $titleClass() }}">{{ $title }}</h3>

        @if($description !== null)
            <p class="{{ $descriptionClass() }}">{{ $description }}</p>
        @endif
    </div>
</div>

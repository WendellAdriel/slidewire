<section {{ $attributes->class([$wrapperClass()]) }}>
    <div class="{{ $mediaClass() }}">
        @if($mediaWrapperClass() !== '')
            <div class="{{ $mediaWrapperClass() }}">{{ $media ?? '' }}</div>
        @else
            {{ $media ?? '' }}
        @endif
    </div>

    <div class="{{ $contentClass() }}">{{ $content ?? '' }}</div>
</section>

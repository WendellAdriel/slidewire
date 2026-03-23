<div {{ $attributes->class([$wrapperClass()]) }}>
    @if($overline !== null)
        <p class="{{ $overlineClass() }}">{{ $overline }}</p>
    @endif

    <div class="space-y-4 sm:space-y-3">
        <h1 class="{{ $headingClass() }}">{{ $title }}</h1>

        @if($subtitle !== null)
            <p class="{{ $bodyClass() }}">{{ $subtitle }}</p>
        @endif
    </div>

    @if($speaker !== null || $event !== null || $date !== null)
        <div class="{{ $metaClass() }}">
            @if($speaker !== null)
                <span>{{ $speaker }}</span>
            @endif
            @if($event !== null)
                <span>{{ $event }}</span>
            @endif
            @if($date !== null)
                <span>{{ $date }}</span>
            @endif
        </div>
    @endif
</div>

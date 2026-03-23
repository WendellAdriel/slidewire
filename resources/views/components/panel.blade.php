@php
    $styles = $styles();
    $bodyClasses = trim($styles['body'] . ' ' . $bodyPadding());
@endphp

<div {{ $attributes->class([$styles['wrapper']]) }}>
    @if($overline !== null || $title !== null)
        <header class="{{ $styles['header'] }}">
            @if($overline !== null)
                <p class="{{ $overlineClass() }}">{{ $overline }}</p>
            @endif

            @if($title !== null)
                <h2 class="{{ $headingClass() }} text-3xl sm:text-2xl">{{ $title }}</h2>
            @endif
        </header>
    @endif

    <div class="{{ $bodyClasses }}">
        <div class="{{ $bodyClass() }} max-w-none">{{ $slot }}</div>
    </div>

    @if($footer !== null)
        <footer class="{{ $styles['footer'] }}">
            <p class="{{ $footerClass() }}">{{ $footer }}</p>
        </footer>
    @endif
</div>

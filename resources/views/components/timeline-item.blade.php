<div {{ $attributes->merge(['data-status' => $status])->when($itemKey !== null, fn ($bag) => $bag->merge(['data-item-key' => (string) $itemKey]))->class([$wrapperClass()]) }}>
    <span class="{{ $markerClass() }}" aria-hidden="true">
        @if($status === 'complete')
            <svg viewBox="0 0 20 20" fill="none" class="{{ $iconClass() }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 10.5l3.25 3.25L15 7" />
            </svg>
        @elseif($status === 'current')
            <svg viewBox="0 0 20 20" fill="none" class="{{ $iconClass() }}" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 3.25l1.9 3.85 4.25.62-3.07 2.99.72 4.22L10 12.95l-3.8 1.99.73-4.22-3.07-2.99 4.24-.62L10 3.25z" />
            </svg>
        @elseif($status === 'upcoming')
            <svg viewBox="0 0 20 20" fill="none" class="{{ $iconClass() }}" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="10" cy="10" r="6.25" />
                <path d="M10 6.75v3.5" />
                <path d="M10 10.25l2.25 1.5" />
            </svg>
        @else
            <svg viewBox="0 0 20 20" fill="none" class="{{ $iconClass() }}" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="10" cy="10" r="3.5" />
            </svg>
        @endif
    </span>

    <div class="min-w-0 space-y-2">
        @if($label !== null)
            <p class="{{ $overlineClass() }}">{{ $label }}</p>
        @endif

        <h3 class="{{ $titleClass() }}">{{ $title }}</h3>

        @if($description !== null)
            <p class="{{ $descriptionClass() }}">{{ $description }}</p>
        @endif
    </div>
</div>

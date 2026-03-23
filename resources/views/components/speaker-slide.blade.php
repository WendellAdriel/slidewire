<section {{ $attributes->merge(['data-variant' => $variant, 'data-align' => $align])->class([$wrapperClass()]) }}>
    @if($avatar !== null)
        <img src="{{ $avatar }}" alt="{{ $name }}" class="{{ $avatarClass() }}" />
    @endif

    <div class="min-w-0 space-y-4 sm:space-y-3">
        <div class="space-y-2">
            <h2 class="{{ $ui->heading($theme) }} max-w-[20ch] text-4xl sm:text-3xl">{{ $name }}</h2>

            @if($role !== null || $company !== null)
                <p class="{{ $ui->meta($theme) }}">
                    {{ collect([$role, $company])->filter()->implode(' - ') }}
                </p>
            @endif
        </div>

        @if(trim((string) $slot) !== '')
            <div class="{{ $ui->body($theme) }} {{ $align === 'center' ? 'mx-auto' : '' }} max-w-none">{{ $slot }}</div>
        @endif
    </div>
</section>

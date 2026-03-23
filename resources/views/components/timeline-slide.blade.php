<div {{ $attributes->merge(['data-orientation' => $orientation])->class([$wrapperClass()]) }}>
    @if($title !== null)
        <div class="space-y-3">
            <h2 class="{{ $ui->heading($theme) }} text-3xl sm:text-2xl">{{ $title }}</h2>
        </div>
    @endif

    <div class="slidewire-timeline-list {{ $listClass() }}">{{ $slot }}</div>
</div>

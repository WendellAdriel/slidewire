@php
    $componentStyle = collect([$attributes->get('style'), $fontFamilyStyle()])
        ->filter()
        ->implode(' ');

    $componentAttributes = $attributes
        ->except('style')
        ->class([
            'slidewire-text',
            'slidewire-text-vertical' => $normalizedOrientation() === 'vertical',
        ])
        ->merge([
            'data-slidewire-animate' => 'true',
            'data-text-type' => $type,
            'data-orientation' => $normalizedOrientation(),
            'data-animation-speed' => $normalizedAnimationSpeed(),
        ])
        ->when($componentStyle !== '', fn ($attributeBag) => $attributeBag->merge(['style' => $componentStyle]))
        ->when($animation !== null, fn ($attributeBag) => $attributeBag->merge(['data-animation' => $animation]));
@endphp

@if($tag() === 'span')
    <span {{ $componentAttributes }}>{{ $slot }}</span>
@elseif($tag() === 'h2')
    <h2 {{ $componentAttributes }}>{{ $slot }}</h2>
@else
    <p {{ $componentAttributes }}>{{ $slot }}</p>
@endif

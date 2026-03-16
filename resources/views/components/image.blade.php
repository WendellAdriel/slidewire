<img
    {{ $attributes
        ->class(['slidewire-image'])
        ->merge([
            'data-slidewire-animate' => 'true',
            'data-animation-speed' => $normalizedAnimationSpeed(),
        ])
        ->when($animation !== null, fn ($attributeBag) => $attributeBag->merge(['data-animation' => $animation])) }}
/>

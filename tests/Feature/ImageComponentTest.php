<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders an image tag and forwards native attributes', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::image
    src="/images/hero.png"
    alt="Hero image"
    class="w-72 rounded-2xl"
    loading="lazy"
    width="320"
/>
BLADE);

    expect($html)
        ->toContain('<img')
        ->toContain('src="/images/hero.png"')
        ->toContain('alt="Hero image"')
        ->toContain('class="slidewire-image w-72 rounded-2xl"')
        ->toContain('loading="lazy"')
        ->toContain('width="320"')
        ->toContain('data-slidewire-animate="true"')
        ->toContain('data-animation-speed="default"');
});

it('emits animation data attributes when provided', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::image src="/images/product.png" alt="Product shot" animation="pop" animation-speed="default" />
BLADE);

    expect($html)
        ->toContain('data-animation="pop"')
        ->toContain('data-animation-speed="default"');
});

it('omits optional animation data when not provided', function (): void {
    $html = Blade::render('<x-slidewire::image src="/images/product.png" alt="Product shot" />');

    expect($html)
        ->not->toContain('data-animation=');
});

it('treats unsupported animation names as plain output rather than erroring', function (): void {
    $html = Blade::render('<x-slidewire::image src="/images/product.png" alt="Product shot" animation="typewriter" />');

    expect($html)
        ->toContain('data-animation="typewriter"');
});

it('falls back safely for invalid animation speed', function (): void {
    $html = Blade::render('<x-slidewire::image src="/images/product.png" alt="Product shot" animation-speed="warp" />');

    expect($html)->toContain('data-animation-speed="default"');
});

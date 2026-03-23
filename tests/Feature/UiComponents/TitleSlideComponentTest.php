<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders title slide content and metadata', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::title-slide
    title="Launch Day"
    subtitle="The story behind the release"
    overline="Product keynote"
    speaker="Wendell Adriel"
    event="SlideWire Live"
    date="March 2026"
/>
BLADE);

    expect($html)
        ->toContain('Launch Day')
        ->toContain('The story behind the release')
        ->toContain('Product keynote')
        ->toContain('Wendell Adriel')
        ->toContain('SlideWire Live')
        ->toContain('March 2026');
});

it('supports alignment and variant changes on title slide', function (): void {
    $html = Blade::render('<x-slidewire::title-slide title="Centered" align="center" variant="hero" />');

    expect($html)
        ->toContain('items-center')
        ->toContain('text-center')
        ->toContain('lg:text-6xl');
});

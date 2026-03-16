<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a paragraph tag by default', function (): void {
    $html = Blade::render('<x-slidewire::text>Hello world</x-slidewire::text>');

    expect($html)
        ->toContain('<p')
        ->toContain('Hello world')
        ->toContain('data-text-type="paragraph"')
        ->toContain('data-orientation="horizontal"')
        ->toContain('data-animation-speed="default"');
});

it('renders span for inline type', function (): void {
    $html = Blade::render('<x-slidewire::text type="inline">Inline</x-slidewire::text>');

    expect($html)->toContain('<span');
});

it('renders h2 for heading type', function (): void {
    $html = Blade::render('<x-slidewire::text type="heading">Heading</x-slidewire::text>');

    expect($html)->toContain('<h2');
});

it('falls back safely for invalid type', function (): void {
    $html = Blade::render('<x-slidewire::text type="unknown">Fallback</x-slidewire::text>');

    expect($html)
        ->toContain('<p')
        ->toContain('data-text-type="paragraph"');
});

it('marks vertical orientation correctly', function (): void {
    $html = Blade::render('<x-slidewire::text orientation="vertical">Vertical</x-slidewire::text>');

    expect($html)
        ->toContain('slidewire-text-vertical')
        ->toContain('data-orientation="vertical"');
});

it('falls back safely for invalid orientation', function (): void {
    $html = Blade::render('<x-slidewire::text orientation="diagonal">Fallback</x-slidewire::text>');

    expect($html)->toContain('data-orientation="horizontal"');
});

it('forwards custom classes and animation data attributes', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::text class="text-5xl font-semibold" animation="slide-up" animation-speed="fast">
    Launch Day
</x-slidewire::text>
BLADE);

    expect($html)
        ->toContain('text-5xl')
        ->toContain('font-semibold')
        ->toContain('data-animation="slide-up"')
        ->toContain('data-animation-speed="fast"');
});

it('falls back safely for invalid animation speed', function (): void {
    $html = Blade::render('<x-slidewire::text animation-speed="warp">Fallback</x-slidewire::text>');

    expect($html)->toContain('data-animation-speed="default"');
});

it('preserves nested inline html inside the slot', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::text>
    Launch <strong>Day</strong>
</x-slidewire::text>
BLADE);

    expect($html)->toContain('<strong>Day</strong>');
});

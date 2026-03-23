<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders two column slide slots with ratio gap and reverse behavior', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::two-column-slide ratio="2:1" gap="xl" reverse="true">
    <x-slot:left>
        <p>Left side</p>
    </x-slot:left>
    <x-slot:right>
        <p>Right side</p>
    </x-slot:right>
</x-slidewire::two-column-slide>
BLADE);

    expect($html)
        ->toContain('Left side')
        ->toContain('Right side')
        ->toContain('lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]')
        ->toContain('lg:order-2')
        ->toContain('gap-9');
});

it('renders media split slide slots and media wrapper styles', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::media-split-slide media-position="right" ratio="3:2" media-style="panel">
    <x-slot:media>
        <img src="/demo.png" alt="Demo screen" />
    </x-slot:media>
    <x-slot:content>
        <p>Supporting copy</p>
    </x-slot:content>
</x-slidewire::media-split-slide>
BLADE);

    expect($html)
        ->toContain('Demo screen')
        ->toContain('Supporting copy')
        ->toContain('lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]')
        ->toContain('lg:order-2')
        ->toContain('backdrop-blur-xl');
});

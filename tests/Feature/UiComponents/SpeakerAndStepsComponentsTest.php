<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('auto numbers step items when no explicit number is supplied', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::steps-slide title="Rollout" columns="2" style="connected">
    <x-slidewire::step-item title="Plan" description="Define the milestone" style="connected" />
    <x-slidewire::step-item title="Ship" description="Release to customers" style="connected" />
</x-slidewire::steps-slide>
BLADE);

    expect($html)
        ->toContain('Rollout')
        ->toContain('Plan')
        ->toContain('Ship')
        ->toContain('data-step-number="auto"')
        ->toContain('before:content-[counter(step)]')
        ->toContain('data-columns="2"');
});

it('renders speaker slide with avatar and spotlight variant', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::speaker-slide
    name="Wendell Adriel"
    role="Creator"
    company="SlideWire"
    avatar="/speaker.webp"
    align="center"
    variant="spotlight"
>
    Building delightful presentation workflows for Laravel teams.
</x-slidewire::speaker-slide>
BLADE);

    expect($html)
        ->toContain('Wendell Adriel')
        ->toContain('Creator - SlideWire')
        ->toContain('/speaker.webp')
        ->toContain('data-variant="spotlight"')
        ->toContain('data-align="center"')
        ->toContain('size-36')
        ->toContain('Building delightful presentation workflows');
});

it('renders speaker slide cleanly without an avatar', function (): void {
    $html = Blade::render('<x-slidewire::speaker-slide name="Guest">Closing keynote</x-slidewire::speaker-slide>');

    expect($html)
        ->toContain('Guest')
        ->toContain('Closing keynote')
        ->not->toContain('<img');
});

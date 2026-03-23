<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders timeline items with status styling', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::timeline-slide title="Roadmap" highlight="shipping">
    <x-slidewire::timeline-item title="Discover" label="Week 1" description="Research and framing" status="complete" />
    <x-slidewire::timeline-item title="Ship" label="Week 4" description="Roll out to customers" status="current" item-key="shipping" />
</x-slidewire::timeline-slide>
BLADE);

    expect($html)
        ->toContain('Roadmap')
        ->toContain('Discover')
        ->toContain('Ship')
        ->toContain('data-highlight="shipping"')
        ->toContain('data-status="complete"')
        ->toContain('data-status="current"');
});

it('renders agenda items with active state and cards layout', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::agenda-slide title="Agenda" subtitle="How we will spend the hour" style="cards" highlight="2">
    <x-slidewire::agenda-item index="1" title="State of the product" description="What changed since last quarter" style="cards" />
    <x-slidewire::agenda-item index="2" title="Launch plan" description="What ships next" style="cards" active />
</x-slidewire::agenda-slide>
BLADE);

    expect($html)
        ->toContain('Agenda')
        ->toContain('How we will spend the hour')
        ->toContain('State of the product')
        ->toContain('Launch plan')
        ->toContain('data-active="true"')
        ->toContain('data-style="cards"');
});

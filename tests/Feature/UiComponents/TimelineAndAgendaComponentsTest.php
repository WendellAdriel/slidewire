<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders timeline items with status styling', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::timeline-slide title="Roadmap">
    <x-slidewire::timeline-item title="Discover" label="Week 1" description="Research and framing" status="complete" />
    <x-slidewire::timeline-item title="Ship" label="Week 4" description="Roll out to customers" status="current" item-key="shipping" />
</x-slidewire::timeline-slide>
BLADE);

    expect($html)
        ->toContain('Roadmap')
        ->toContain('Discover')
        ->toContain('Ship')
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
        ->toContain('grid-cols-1')
        ->toContain('data-active="true"')
        ->toContain('data-style="cards"');
});

it('renders agenda variants with distinct structural treatments', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::agenda-item index="1" title="Overview" description="Summary" style="list" />
<x-slidewire::agenda-item index="2" title="List active" description="Highlighted list item" style="list" active />
<x-slidewire::agenda-item index="3" title="Details" description="Deeper dive" style="cards" active />
<x-slidewire::agenda-item index="4" title="Next" description="Follow-up" style="timeline" />
BLADE);

    expect($html)
        ->toContain('border-b pb-4')
        ->toContain('text-lg font-medium')
        ->toContain('shadow-[0_18px_50px_rgba(15,23,42,0.10)]')
        ->toContain('rounded-r-[1.5rem]')
        ->toContain('before:absolute')
        ->toContain('before:w-1')
        ->toContain('border-2 bg-white text-sm/6 font-semibold')
        ->toContain('shadow-[0_0_0_6px_rgba(255,255,255,0.9)]');
});

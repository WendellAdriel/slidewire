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

it('renders minimal steps differently from card steps', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::steps-slide title="Workflow" columns="1" style="minimal">
    <x-slidewire::step-item title="Draft" description="Shape the story" style="minimal" />
</x-slidewire::steps-slide>

<x-slidewire::steps-slide title="Workflow" columns="1" style="cards">
    <x-slidewire::step-item title="Draft" description="Shape the story" style="cards" />
</x-slidewire::steps-slide>
BLADE);

    expect($html)
        ->toContain('data-style="minimal"')
        ->toContain('border-l')
        ->toContain('size-7')
        ->toContain('text-base font-medium')
        ->toContain('text-sm/6 sm:text-[0.8125rem]/6')
        ->toContain('data-style="cards"')
        ->toContain('size-11')
        ->toContain('text-xl font-semibold');
});

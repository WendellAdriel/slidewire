<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders panel content with title overline and footer', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::panel title="Launch status" overline="Quarterly update" footer="Next review on Friday">
    Momentum is compounding across every launch channel.
</x-slidewire::panel>
BLADE);

    expect($html)
        ->toContain('Launch status')
        ->toContain('Quarterly update')
        ->toContain('Next review on Friday')
        ->toContain('Momentum is compounding');
});

it('applies panel variants and tones', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::panel variant="glass" tone="warning">
    Watch the migration window.
</x-slidewire::panel>
BLADE);

    expect($html)
        ->toContain('backdrop-blur-xl')
        ->toContain('border-amber-300/24');
});

it('preserves custom classes while keeping base panel styles', function (): void {
    $html = Blade::render(<<<'BLADE'
<x-slidewire::panel class="ring-4 ring-cyan-400/30">
    Custom panel
</x-slidewire::panel>
BLADE);

    expect($html)
        ->toContain('ring-4')
        ->toContain('ring-cyan-400/30')
        ->toContain('rounded-[2rem]')
        ->toContain('Custom panel');
});

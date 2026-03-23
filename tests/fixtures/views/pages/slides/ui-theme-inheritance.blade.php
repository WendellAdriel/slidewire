<?php

use Livewire\Component;

new class() extends Component
{
    //
}; ?>

<x-slidewire::deck theme="white">
    <x-slidewire::slide>
        <x-slidewire::panel title="Theme inheritance" overline="Deck theme" tone="primary">
            <x-slidewire::text type="heading">Readable on light decks</x-slidewire::text>
        </x-slidewire::panel>
    </x-slidewire::slide>

    <x-slidewire::slide theme="black">
        <x-slidewire::panel title="Theme inheritance" overline="Slide override" variant="glass">
            <x-slidewire::text type="heading">Readable on dark slides</x-slidewire::text>
        </x-slidewire::panel>
    </x-slidewire::slide>
</x-slidewire::deck>

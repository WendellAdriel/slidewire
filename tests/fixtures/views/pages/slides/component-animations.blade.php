<?php

use Livewire\Component;

new class() extends Component
{
    //
}; ?>

<x-slidewire::deck>
    <x-slidewire::slide class="bg-slate-950 text-white">
        <x-slidewire::text
            type="heading"
            orientation="vertical"
            animation="slide-up"
            animation-speed="slow"
            class="text-5xl font-semibold"
        >
            Launch Day
        </x-slidewire::text>

        <x-slidewire::text animation="typewriter" animation-speed="slow" class="mt-8 text-2xl">
            Components that animate with the deck runtime.
        </x-slidewire::text>
    </x-slidewire::slide>

    <x-slidewire::slide class="bg-white text-slate-900">
        <x-slidewire::image
            src="/images/product-shot.png"
            alt="Product shot"
            class="w-72 rounded-2xl shadow-2xl"
            loading="lazy"
            animation="pop"
            animation-speed="default"
        />

        <x-slidewire::text type="inline" animation="blur" animation-speed="fast" class="mt-6 text-xl">
            Product reveal
        </x-slidewire::text>
    </x-slidewire::slide>
</x-slidewire::deck>

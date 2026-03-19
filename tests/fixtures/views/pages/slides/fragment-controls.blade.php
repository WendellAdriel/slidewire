<?php

use Livewire\Component;

new class() extends Component
{
    //
}; ?>

<x-slidewire::deck>
    <x-slidewire::slide class="bg-slate-900 text-white">
        <h1>First Edge</h1>

        <x-slidewire::fragment :index="0">
            <p>First fragment</p>
        </x-slidewire::fragment>

        <x-slidewire::fragment :index="1">
            <p>Second fragment</p>
        </x-slidewire::fragment>
    </x-slidewire::slide>

    <x-slidewire::slide class="bg-slate-800 text-white">
        <h1>Middle Slide</h1>
    </x-slidewire::slide>

    <x-slidewire::slide class="bg-slate-700 text-white">
        <h1>Last Edge</h1>

        <x-slidewire::fragment :index="0">
            <p>Last fragment</p>
        </x-slidewire::fragment>

        <x-slidewire::fragment :index="1">
            <p>Final fragment</p>
        </x-slidewire::fragment>
    </x-slidewire::slide>
</x-slidewire::deck>

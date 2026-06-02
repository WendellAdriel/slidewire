<?php

use Livewire\Component;

new class extends Component {
    //
}; ?>

<x-slidewire::deck transition="slide" transition-duration="220" theme="black">
    <x-slidewire::slide class="bg-slate-950 text-white">
        <div style="display: grid; gap: 1rem; text-align: center;">
            <p style="text-transform: uppercase; letter-spacing: .22em; color: #38bdf8;">Offset Regression</p>
            <h1 style="font-size: clamp(3rem, 8vw, 8rem); margin: 0;">Offset Intro</h1>
        </div>
    </x-slidewire::slide>

    <x-slidewire::slide class="bg-slate-900 text-white">
        <div style="display: grid; gap: 1.25rem;">
            <h2 style="font-size: clamp(2.25rem, 5vw, 5.25rem); margin: 0;">Wide Grid</h2>
            <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem;">
                @foreach (range(1, 8) as $item)
                    <div style="min-height: 8rem; border-radius: 1.25rem; padding: 1.25rem; background: rgba(14, 165, 233, .16); border: 1px solid rgba(125, 211, 252, .28);">
                        <strong>Panel {{ $item }}</strong>
                        <p>Dense content keeps this slide close to the showcase layout width.</p>
                    </div>
                @endforeach
            </div>
        </div>
    </x-slidewire::slide>

    <x-slidewire::slide transition="fade" class="bg-sky-950 text-white">
        <div style="display: grid; gap: 1rem; text-align: center;">
            <h2 style="font-size: clamp(2.5rem, 6vw, 6rem); margin: 0;">Fade Metrics</h2>
            <p style="font-size: 1.5rem;">A non-slide transition must clean up frame opacity animations.</p>
        </div>
    </x-slidewire::slide>

    <x-slidewire::slide transition="zoom" class="bg-indigo-950 text-white">
        <div style="display: grid; grid-template-columns: 1.1fr .9fr; gap: 2rem; align-items: center;">
            <div>
                <h2 style="font-size: clamp(2.75rem, 7vw, 7rem); margin: 0;">Zoom Cards</h2>
                <p style="font-size: 1.5rem;">The active frame should return to layout-owned transform state.</p>
            </div>
            <div style="display: grid; gap: 1rem;">
                <div style="height: 9rem; border-radius: 1.5rem; background: rgba(129, 140, 248, .28);"></div>
                <div style="height: 9rem; border-radius: 1.5rem; background: rgba(56, 189, 248, .22);"></div>
            </div>
        </div>
    </x-slidewire::slide>

    <x-slidewire::vertical-slide>
        <x-slidewire::slide class="bg-emerald-950 text-white">
            <div style="display: grid; gap: 1rem; text-align: center;">
                <h2 style="font-size: clamp(2.5rem, 6vw, 6rem); margin: 0;">Vertical Overview</h2>
                <p style="font-size: 1.5rem;">Horizontal entry to a vertical stack.</p>
            </div>
        </x-slidewire::slide>

        <x-slidewire::slide class="bg-teal-950 text-white">
            <div style="display: grid; gap: 1rem; text-align: center;">
                <h2 style="font-size: clamp(2.5rem, 6vw, 6rem); margin: 0;">Vertical Detail</h2>
                <p style="font-size: 1.5rem;">Vertical navigation must also clear translateY animations.</p>
            </div>
        </x-slidewire::slide>
    </x-slidewire::vertical-slide>
</x-slidewire::deck>

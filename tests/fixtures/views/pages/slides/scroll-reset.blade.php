<?php

use Livewire\Component;

new class extends Component {
    //
}; ?>

<x-slidewire::deck>
    <x-slidewire::slide>
        <h1 style="font-size: 2rem; font-weight: bold; color: #fff; margin: 0 0 0.5rem;">AI Ready</h1>
        <p style="color: #cbd5e1; font-size: 1.1rem; margin: 0 0 2rem;">SlideWire ships with a Boost AI skill for beautiful deck creation.</p>

        <x-slidewire::fragment>
            <div style="background: #7c3aed; color: #fff; border-radius: 1rem; padding: 2rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 0.5rem;">Workflow Aware</h3>
                <p style="margin: 0;">Steers agents toward make:slidewire, single-file decks, and route macro registration.</p>
            </div>
        </x-slidewire::fragment>

        <x-slidewire::fragment>
            <div style="background: #2563eb; color: #fff; border-radius: 1rem; padding: 2rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 0.5rem;">Component Smart</h3>
                <p style="margin: 0;">Helps agents reach for slides, fragments, markdown, code, and diagrams at the right time.</p>
            </div>
        </x-slidewire::fragment>

        <x-slidewire::fragment>
            <div style="background: #059669; color: #fff; border-radius: 1rem; padding: 2rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 0.5rem;">Design Guided</h3>
                <p style="margin: 0;">Encodes layout patterns, color rules, and typography so AI output looks polished.</p>
            </div>
        </x-slidewire::fragment>

        <x-slidewire::fragment>
            <div style="background: #d97706; color: #fff; border-radius: 1rem; padding: 2rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 0.5rem;">Context Loaded</h3>
                <p style="margin: 0;">Provides full package context so the agent never guesses at API surfaces.</p>
            </div>
        </x-slidewire::fragment>

        <x-slidewire::fragment>
            <div style="background: #dc2626; color: #fff; border-radius: 1rem; padding: 2rem;">
                <h3 style="font-size: 1.1rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 0.5rem;">Quality Enforced</h3>
                <p style="margin: 0;">Runs lint, type-check, and test gates automatically.</p>
            </div>
        </x-slidewire::fragment>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <h1 style="font-size: 2rem; font-weight: bold; color: #fff; margin: 0 0 0.5rem;">Getting Started</h1>
        <p style="color: #cbd5e1; font-size: 1.1rem; margin: 0 0 2rem;">Install and create your first presentation.</p>

        <x-slidewire::fragment>
            <div style="background: #be185d; color: #fff; border-radius: 1rem; padding: 2rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 0.5rem;">Step 1: Install</h3>
                <p style="margin: 0;">Run composer require slidewire/slidewire and publish the config.</p>
            </div>
        </x-slidewire::fragment>

        <x-slidewire::fragment>
            <div style="background: #0d9488; color: #fff; border-radius: 1rem; padding: 2rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 0.5rem;">Step 2: Scaffold</h3>
                <p style="margin: 0;">Use php artisan make:slidewire to generate your deck boilerplate.</p>
            </div>
        </x-slidewire::fragment>

        <x-slidewire::fragment>
            <div style="background: #ca8a04; color: #fff; border-radius: 1rem; padding: 2rem;">
                <h3 style="font-size: 1.1rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 0.5rem;">Step 3: Present</h3>
                <p style="margin: 0;">Open in any browser — keyboard, touch, and fullscreen all work out of the box.</p>
            </div>
        </x-slidewire::fragment>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <div style="text-align: center;">
            <h1 style="font-size: 2rem; font-weight: bold; color: #fff;">Short Slide</h1>
            <p style="color: #94a3b8; font-size: 1.25rem;">This one fits without scrolling.</p>
        </div>
    </x-slidewire::slide>
</x-slidewire::deck>

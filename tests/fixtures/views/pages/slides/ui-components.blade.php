<?php

use Livewire\Component;

new class() extends Component
{
    //
}; ?>

<x-slidewire::deck theme="neon" transition="fade">
    <x-slidewire::slide>
        <x-slidewire::title-slide
            title="SlideWire Launch Kit"
            subtitle="A first-party set of polished presentation components for modern Blade decks."
            overline="Presentation systems"
            speaker="Wendell Adriel"
            event="SlideWire Summit"
            date="March 2026"
            variant="hero"
            align="center"
        ></x-slidewire::title-slide>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <x-slidewire::two-column-slide ratio="2:1" gap="xl" align="center">
            <x-slot name="left">
                <div class="space-y-5 sm:space-y-4">
                    <x-slidewire::panel title="Why teams switch" overline="Less boilerplate" footer="Compose these inside any deck" variant="glass">
                        <p>Authors can build sharp title cards, agendas, timelines, and speaker profiles without handcrafting the same Tailwind wrappers on every slide.</p>
                    </x-slidewire::panel>
                </div>
            </x-slot>
            <x-slot name="right">
                <x-slidewire::steps-slide title="Rollout" columns="1" style="cards">
                    <x-slidewire::step-item title="Plan" description="Outline the narrative and choose a theme."></x-slidewire::step-item>
                    <x-slidewire::step-item title="Compose" description="Mix panels, split layouts, and agenda blocks."></x-slidewire::step-item>
                    <x-slidewire::step-item title="Present" description="Ship a polished deck with less repeated markup."></x-slidewire::step-item>
                </x-slidewire::steps-slide>
            </x-slot>
        </x-slidewire::two-column-slide>
    </x-slidewire::slide>

    <x-slidewire::slide theme="white">
        <x-slidewire::two-column-slide ratio="1:2" gap="xl" align="center" reverse="true">
            <x-slot name="left">
                <x-slidewire::panel title="Narrative layout" overline="Two column" tone="primary" variant="elevated">
                    <x-slidewire::text type="heading" font="Inter">Build atmosphere</x-slidewire::text>
                    <x-slidewire::text>Use bright surfaces on light themes and translucent panels on darker decks without losing contrast.</x-slidewire::text>
                </x-slidewire::panel>
            </x-slot>
            <x-slot name="right">
                <x-slidewire::agenda-slide title="Agenda" subtitle="A chapter view that can stay simple or get more visual" style="cards">
                    <x-slidewire::agenda-item index="1" title="The problem" description="Why repeated wrappers slow authors down" style="cards"></x-slidewire::agenda-item>
                    <x-slidewire::agenda-item index="2" title="The system" description="A shared UI resolver and focused Blade components" style="cards" active></x-slidewire::agenda-item>
                </x-slidewire::agenda-slide>
            </x-slot>
        </x-slidewire::two-column-slide>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <x-slidewire::timeline-slide title="Delivery plan">
            <x-slidewire::timeline-item title="Foundation" label="Phase 1" description="Ship shared UI tokens and a panel primitive." status="complete"></x-slidewire::timeline-item>
            <x-slidewire::timeline-item title="Layouts" label="Phase 2" description="Add title, split, agenda, and speaker components." status="current" item-key="shipping"></x-slidewire::timeline-item>
            <x-slidewire::timeline-item title="Polish" label="Phase 3" description="Validate with fixtures and browser smoke tests." status="upcoming"></x-slidewire::timeline-item>
        </x-slidewire::timeline-slide>
    </x-slidewire::slide>

    <x-slidewire::slide>
        <div class="mx-auto flex min-h-full w-full max-w-5xl items-center">
            <x-slidewire::panel title="Meet the speaker" overline="Built from primitives" variant="elevated" tone="primary" class="w-full">
                <div class="flex items-center gap-5">
                    <img src="https://assets.example.test/avatars/1.webp" alt="Wendell Adriel" class="size-24 rounded-full object-cover outline-1 -outline-offset-1 outline-white/10" />
                    <div class="space-y-2">
                        <h3 class="text-3xl font-semibold text-white">Wendell Adriel</h3>
                        <p class="text-sm/6 text-cyan-100/70">Maintainer - SlideWire</p>
                        <p class="max-w-none text-lg/7 text-cyan-100/90">Meet the speaker and close the deck with the same visual language used by the rest of the component set.</p>
                    </div>
                </div>
            </x-slidewire::panel>
        </div>
    </x-slidewire::slide>
</x-slidewire::deck>

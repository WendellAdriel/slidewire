<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('renders a deck fixture using the new ui components', function (): void {
    Route::slidewire('/slides/ui-components', 'ui-components');

    test()->get('/slides/ui-components')
        ->assertSuccessful()
        ->assertSee('SlideWire Launch Kit')
        ->assertSee('Why teams switch')
        ->assertSee('Meet the speaker')
        ->assertSee('Agenda');
});

it('inherits deck and slide theme context inside ui component fixtures', function (): void {
    Route::slidewire('/slides/ui-theme-inheritance', 'ui-theme-inheritance');

    $content = test()->get('/slides/ui-theme-inheritance')->getContent();

    expect($content)
        ->toContain('Theme inheritance')
        ->toContain('text-zinc-950')
        ->toContain('text-white');
});

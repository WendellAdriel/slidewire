<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('renders a route-backed composed presentation in the expected order', function (): void {
    Route::slidewire('/slides/composed-launch', 'composed/product-launch');

    test()->get('/slides/composed-launch')
        ->assertSuccessful()
        ->assertSee('Launch Intro')
        ->assertSee('Live Demo')
        ->assertSee('Roadmap');
});

it('applies deck defaults from the composed deck wrapper', function (): void {
    Route::slidewire('/slides/composed-theme', 'composed/product-launch');

    $content = test()->get('/slides/composed-theme')->getContent();

    expect($content)->toContain('slidewire-theme-black')
        ->and($content)->toContain('data-transition="fade"');
});

it('surfaces nested deck wrapper failures for composed presentations', function (): void {
    Route::slidewire('/slides/composed-invalid', 'composed/invalid-nested-deck');

    test()->get('/slides/composed-invalid')->assertStatus(500);
});

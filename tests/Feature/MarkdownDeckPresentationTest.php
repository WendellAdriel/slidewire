<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('renders a route-backed markdown presentation', function (): void {
    Route::slidewire('/slides/markdown-standalone', 'markdown/standalone');

    test()->get('/slides/markdown-standalone')
        ->assertSuccessful()
        ->assertSee('Welcome to SlideWire.')
        ->assertSee('phiki')
        ->assertDontSee('Previous slide');
});

it('applies deck and slide metadata from markdown presentations to rendered output', function (): void {
    Route::slidewire('/slides/markdown-meta', 'markdown/standalone');

    $content = test()->get('/slides/markdown-meta')->getContent();

    expect($content)->toContain('slidewire-theme-black')
        ->and($content)->toContain('slidewire-theme-white')
        ->and($content)->toContain('data-auto-slide="3000"');
});

it('returns the existing 404 behavior for empty markdown presentations', function (): void {
    Route::slidewire('/slides/markdown-empty', 'markdown/standalone-empty');

    test()->get('/slides/markdown-empty')->assertNotFound();
});

it('surfaces invalid markdown compile failures clearly', function (): void {
    Route::slidewire('/slides/markdown-invalid', 'markdown/standalone-invalid-frontmatter');

    test()->get('/slides/markdown-invalid')->assertStatus(500);
});

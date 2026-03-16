<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('returns 404 for non-existent presentation', function (): void {
    Route::slidewire('/slides/nonexistent', 'nonexistent-deck');

    test()->get('/slides/nonexistent')
        ->assertNotFound();
});

it('renders progress bar when show_progress is enabled', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    test()->get('/slides/demo')
        ->assertSuccessful()
        ->assertSee('slidewire-progress');
});

it('renders control arrows for navigation', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    test()->get('/slides/demo')
        ->assertSuccessful()
        ->assertSee('slidewire-controls')
        ->assertSee('Previous slide')
        ->assertSee('Next slide');
});

it('renders vertical controls for decks with vertical slides', function (): void {
    Route::slidewire('/slides/vertical', 'vertical');

    test()->get('/slides/vertical')
        ->assertSuccessful()
        ->assertSee('Slide up')
        ->assertSee('Slide down');
});

it('does not render vertical controls for flat decks', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    test()->get('/slides/demo')
        ->assertSuccessful()
        ->assertDontSee('Slide up')
        ->assertDontSee('Slide down');
});

it('renders fullscreen button by default', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    test()->get('/slides/demo')
        ->assertSuccessful()
        ->assertSee('Enter fullscreen');
});

it('renders slides with data attributes for transitions', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    $response = test()->get('/slides/demo');
    $content = $response->getContent();

    expect($content)->toContain('data-transition=');
});

it('renders background fixture with background-related content', function (): void {
    Route::slidewire('/slides/background', 'background');

    test()->get('/slides/background')
        ->assertSuccessful()
        ->assertSee('Background Demo');
});

it('supports hash deep-linking via data-h and data-v attributes', function (): void {
    Route::slidewire('/slides/vertical', 'vertical');

    $response = test()->get('/slides/vertical');
    $content = $response->getContent();

    expect($content)->toContain('data-h=')
        ->and($content)->toContain('data-v=');
});

it('renders slidewire-stage container', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    test()->get('/slides/demo')
        ->assertSuccessful()
        ->assertSee('slidewire-stage');
});

it('injects alpine slidewireDeck function', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    $response = test()->get('/slides/demo');
    $content = $response->getContent();

    expect($content)->toContain('slidewireDeck');
});

it('allows active slides to scroll vertically when content overflows', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    $response = test()->get('/slides/demo');
    $content = $response->getContent();

    expect($content)
        ->toContain('overflow-y: auto')
        ->toContain('-webkit-overflow-scrolling: touch')
        ->toContain('margin-block: auto');
});

it('preserves vertical scroll gestures before triggering vertical navigation', function (): void {
    Route::slidewire('/slides/vertical', 'vertical');

    $response = test()->get('/slides/vertical');
    $content = $response->getContent();

    expect($content)
        ->toContain('onArrowKey')
        ->toContain('shouldPreserveVerticalScroll')
        ->toContain('canScrollActiveSlide');
});

it('includes package css hooks for text, image, and typewriter animations', function (): void {
    Route::slidewire('/slides/component-animations', 'component-animations');

    $content = test()->get('/slides/component-animations')->getContent();

    expect($content)
        ->toContain('.slidewire-text')
        ->toContain('.slidewire-image')
        ->toContain('.slidewire-text-vertical')
        ->toContain('.slidewire-typewriter');
});

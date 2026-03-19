<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('scrolls new slide to top when navigating on mobile viewport', function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);

    Route::slidewire('/slides/scroll-reset', 'scroll-reset');

    $page = visit('/slides/scroll-reset');

    $page->resize(375, 667)
        ->assertSee('AI Ready')
        ->assertNoJavaScriptErrors();

    // Scroll the first slide down.
    $page->script("document.querySelector('.slidewire-frame.is-active').scrollTo(0, 9999)");
    $page->wait(0.3);

    $scrollBefore = $page->script("document.querySelector('.slidewire-frame.is-active').scrollTop");
    expect($scrollBefore)->toBeGreaterThan(0);

    // Click the stage to advance through all fragments, then to the next slide.
    for ($i = 0; $i < 6; ++$i) {
        $page->script("document.querySelector('.slidewire-stage').click()");
        $page->wait(0.5);
    }

    // The new slide should be scrolled to the top.
    $scrollAfter = $page->script("document.querySelector('.slidewire-frame.is-active').scrollTop");
    expect($scrollAfter)->toBe(0);

    $page->assertSee('Getting Started')
        ->assertNoJavaScriptErrors();
})->group('browser');

it('scrolls fragment into view on mobile viewport', function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);

    Route::slidewire('/slides/scroll-reset', 'scroll-reset');

    $page = visit('/slides/scroll-reset');

    $page->resize(375, 667)
        ->assertSee('AI Ready')
        ->assertNoJavaScriptErrors();

    // Advance through all fragments by clicking the stage.
    for ($i = 0; $i < 5; ++$i) {
        $page->script("document.querySelector('.slidewire-stage').click()");
        $page->wait(0.5);
    }

    // The last fragment should be visible.
    $page->assertSee('Quality Enforced')
        ->assertNoJavaScriptErrors();
})->group('browser');

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('navigates a standalone markdown deck in the browser', function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);

    Route::slidewire('/slides/browser-markdown', 'markdown/standalone');

    $page = visit('/slides/browser-markdown');

    $page->waitForText('Welcome to SlideWire.')
        ->assertSee('Welcome to SlideWire.')
        ->assertNoJavaScriptErrors();

    expect($page->script("document.querySelector('.slidewire-frame.is-active h1').textContent.trim()"))
        ->toBe('Intro')
        ->and($page->script("document.querySelector('.slidewire-controls')"))
        ->toBeNull();

    $page->script("document.querySelector('.slidewire-stage').click()");
    $page->wait(0.5);

    expect($page->script("document.querySelector('.slidewire-frame.is-active h2').textContent.trim()"))
        ->toBe('Code')
        ->and($page->script('window.location.hash'))
        ->toBe('#/slide/2')
        ->and($page->script("document.querySelector('.slidewire-frame.is-active').dataset.theme"))
        ->toBe('white')
        ->and($page->script("document.querySelector('.slidewire-frame.is-active').dataset.autoSlide"))
        ->toBe('3000');

    $page->assertNoJavaScriptErrors();
})->group('browser');

it('navigates a composed presentation in the browser and preserves ordering', function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);

    Route::slidewire('/slides/browser-composed', 'composed/product-launch');

    $page = visit('/slides/browser-composed');

    $page->waitForText('Launch Intro')
        ->assertSee('Launch Intro')
        ->assertNoJavaScriptErrors();

    expect($page->script("document.querySelector('.slidewire-frame.is-active h1').textContent.trim()"))
        ->toBe('Launch Intro')
        ->and($page->script("document.querySelector('.slidewire-shell').className"))
        ->toContain('bg-slate-900');

    $page->script("document.querySelector('.slidewire-stage').click()");
    $page->wait(0.5);

    expect($page->script("document.querySelector('.slidewire-frame.is-active h2').textContent.trim()"))
        ->toBe('Live Demo')
        ->and($page->script('window.location.hash'))
        ->toBe('#/slide/2');

    $page->script("document.querySelector('.slidewire-stage').click()");
    $page->wait(0.5);

    expect($page->script("document.querySelector('.slidewire-frame.is-active h2').textContent.trim()"))
        ->toBe('Roadmap')
        ->and($page->script('window.location.hash'))
        ->toBe('#/slide/3');

    $page->assertNoJavaScriptErrors();
})->group('browser');

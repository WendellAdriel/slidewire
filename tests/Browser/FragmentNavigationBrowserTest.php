<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('keeps fragment controls enabled while fragment steps remain', function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);

    Route::slidewire('/slides/fragment-controls', 'fragment-controls');

    $page = visit('/slides/fragment-controls');

    $page->waitForText('First Edge')
        ->assertSee('First Edge')
        ->assertNoJavaScriptErrors();

    $page->script("document.querySelector('.slidewire-control-right').click()");
    $page->wait(0.5);

    expect($page->script("document.querySelector('.slidewire-control-left').disabled"))
        ->toBeFalse();

    for ($i = 0; $i < 3; ++$i) {
        $page->script("document.querySelector('.slidewire-stage').click()");
        $page->wait(0.5);
    }

    $page->assertSee('Last Edge')
        ->assertNoJavaScriptErrors();

    expect($page->script("document.querySelector('.slidewire-control-right').disabled"))
        ->toBeFalse();
})->group('browser');

it('persists fragment position in the hash and restores it with browser navigation', function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);

    Route::slidewire('/slides/fragments', 'fragments');

    $page = visit('/slides/fragments');

    $page->waitForText('Fragment Slide')
        ->assertSee('Fragment Slide')
        ->assertNoJavaScriptErrors();

    for ($i = 0; $i < 2; ++$i) {
        $page->script("document.querySelector('.slidewire-stage').click()");
        $page->wait(0.5);
    }

    expect($page->script('window.location.hash'))->toBe('#/slide/1/f/1')
        ->and($page->script("document.querySelectorAll('.slidewire-frame.is-active [data-fragment].slidewire-fragment-visible').length"))
        ->toBe(2);

    $page->script('window.history.back()');
    $page->wait(0.5);

    expect($page->script('window.location.hash'))->toBe('#/slide/1/f/0')
        ->and($page->script("document.querySelectorAll('.slidewire-frame.is-active [data-fragment].slidewire-fragment-visible').length"))
        ->toBe(1);

    $page->script('window.history.forward()');
    $page->wait(0.5);

    expect($page->script('window.location.hash'))->toBe('#/slide/1/f/1')
        ->and($page->script("document.querySelectorAll('.slidewire-frame.is-active [data-fragment].slidewire-fragment-visible').length"))
        ->toBe(2);

    $page->assertNoJavaScriptErrors();
})->group('browser');

it('returns to the previous slide fragment state in one back navigation step', function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);

    Route::slidewire('/slides/fragments', 'fragments');

    $page = visit('/slides/fragments');

    $page->waitForText('Fragment Slide')
        ->assertSee('Fragment Slide')
        ->assertNoJavaScriptErrors();

    for ($i = 0; $i < 4; ++$i) {
        $page->script("document.querySelector('.slidewire-stage').click()");
        $page->wait(0.5);
    }

    expect($page->script('window.location.hash'))->toBe('#/slide/2');

    $page->script('window.history.back()');
    $page->wait(0.5);

    expect($page->script('window.location.hash'))->toBe('#/slide/1/f/2')
        ->and($page->script("document.querySelector('.slidewire-frame.is-active h1').textContent.trim()"))
        ->toBe('Fragment Slide')
        ->and($page->script("document.querySelectorAll('.slidewire-frame.is-active [data-fragment].slidewire-fragment-visible').length"))
        ->toBe(3);

    $page->assertNoJavaScriptErrors();
})->group('browser');

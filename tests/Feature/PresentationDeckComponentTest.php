<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use WendellAdriel\SlideWire\DTOs\ThemeConfig;

it('renders a presentation deck through the slidewire route macro', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    test()->get('/slides/demo')
        ->assertSuccessful()
        ->assertSee('Demo Intro')
        ->assertSee('Final slide')
        ->assertSee('Enter fullscreen');
});

it('renders a vertical-slide presentation through the slidewire route macro', function (): void {
    Route::slidewire('/slides/vertical', 'vertical');

    test()->get('/slides/vertical')
        ->assertSuccessful()
        ->assertSee('Horizontal Slide 1')
        ->assertSee('Stack Top')
        ->assertSee('Stack Middle')
        ->assertSee('Stack Bottom')
        ->assertSee('Horizontal Slide 3');
});

it('renders a deck with precedence settings', function (): void {
    Route::slidewire('/slides/precedence', 'precedence');

    test()->get('/slides/precedence')
        ->assertSuccessful()
        ->assertSee('Inherits Deck Defaults')
        ->assertSee('Overrides Deck Settings');
});

it('renders theme background classes from nested theme config', function (): void {
    Route::slidewire('/slides/precedence', 'precedence');

    $response = test()->get('/slides/precedence');
    $response->assertSuccessful();

    $themes = config('slidewire.themes', []);
    $nightBackground = $themes['black'] ?? null;

    expect($nightBackground)->toBeInstanceOf(ThemeConfig::class)
        ->and($nightBackground->background)->not->toBeEmpty();
});

it('includes @vite directive in the blank layout template', function (): void {
    $layoutPath = realpath(__DIR__ . '/../../resources/views/layouts/blank.blade.php');

    expect($layoutPath)->not->toBeFalse()
        ->and(file_get_contents($layoutPath))->toContain('@vite');
});

it('applies slidewire-theme class to slide frame elements', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    $response = test()->get('/slides/demo');
    $content = $response->getContent();

    expect($content)->toContain('slidewire-theme-default');
});

it('applies slide-level theme class when slide overrides theme', function (): void {
    Route::slidewire('/slides/precedence', 'precedence');

    $response = test()->get('/slides/precedence');
    $content = $response->getContent();

    expect($content)->toContain('slidewire-theme-black')
        ->and($content)->toContain('slidewire-theme-white');
});

it('applies text typography classes to slidewire-content div', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    $response = test()->get('/slides/demo');
    $content = $response->getContent();

    expect($content)->toContain('slidewire-content Inter text-slate-200 text-lg');
});

it('applies correct typography classes when slide overrides theme', function (): void {
    Route::slidewire('/slides/precedence', 'precedence');

    $response = test()->get('/slides/precedence');
    $content = $response->getContent();

    expect($content)->toContain('slidewire-content Inter text-zinc-600 text-lg');

    expect($content)->toContain('slidewire-content Inter text-slate-300 text-lg');
});

it('preserves per-slide Tailwind background classes on frame elements', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    $response = test()->get('/slides/demo');
    $content = $response->getContent();

    expect($content)->toContain('bg-slate-900 text-white');
});

it('preserves per-slide gradient classes on frame elements', function (): void {
    Route::slidewire('/slides/gradient', 'gradient');

    $response = test()->get('/slides/gradient');
    $content = $response->getContent();

    expect($content)->toContain('bg-gradient-to-br from-blue-900 to-slate-950 text-slate-50');
});

it('applies white theme class when slide overrides theme in gradient deck', function (): void {
    Route::slidewire('/slides/gradient', 'gradient');

    $response = test()->get('/slides/gradient');
    $content = $response->getContent();

    expect($content)->toContain('slidewire-theme-white');
});

it('passes configured themes to Alpine as background class data', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    $response = test()->get('/slides/demo');
    $content = $response->getContent();

    expect($content)->toContain('bg-gradient-to-br from-slate-900 via-blue-950 to-slate-950');
});

it('passes theme typography data to Alpine component', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    $response = test()->get('/slides/demo');
    $content = $response->getContent();

    expect($content)->toContain('text-slate-50')
        ->and($content)->toContain('text-4xl')
        ->and($content)->toContain('text-slate-200')
        ->and($content)->toContain('text-lg');
});

it('uses configured highlight font size class in deck output', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    $response = test()->get('/slides/demo');
    $content = $response->getContent();

    expect($content)->toContain('text-base');
});

it('includes Mermaid loading logic in deck JavaScript', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    $response = test()->get('/slides/demo');
    $content = $response->getContent();

    expect($content)->toContain('renderDiagrams')
        ->and($content)->toContain('data-slidewire-diagram');
});

it('includes renderDiagrams method in deck JavaScript', function (): void {
    Route::slidewire('/slides/demo', 'demo');

    $response = test()->get('/slides/demo');
    $content = $response->getContent();

    expect($content)->toContain('renderDiagrams');
});

it('renders text and image components inside a real deck', function (): void {
    Route::slidewire('/slides/component-animations', 'component-animations');

    test()->get('/slides/component-animations')
        ->assertSuccessful()
        ->assertSee('Launch Day')
        ->assertSee('Product reveal')
        ->assertSee('Product shot');
});

it('preserves component animation metadata and vertical text hooks in deck output', function (): void {
    Route::slidewire('/slides/component-animations', 'component-animations');

    $content = test()->get('/slides/component-animations')->getContent();

    expect($content)
        ->toContain('data-slidewire-animate="true"')
        ->toContain('data-animation="slide-up"')
        ->toContain('data-animation="pop"')
        ->toContain('data-animation-speed="default"')
        ->toContain('data-animation-speed="fast"')
        ->toContain('slidewire-text-vertical')
        ->toContain('data-orientation="vertical"');
});

it('includes element animation helper logic in deck JavaScript', function (): void {
    Route::slidewire('/slides/component-animations', 'component-animations');

    $content = test()->get('/slides/component-animations')->getContent();

    expect($content)
        ->toContain('animatedNodes(slide)')
        ->toContain('playElementAnimations(slide, phase)')
        ->toContain('animationKeyframes(name, phase, node)')
        ->toContain('animationDurationMultiplier(speed)')
        ->toContain('resetAnimatedNodes(slide)')
        ->toContain('prefers-reduced-motion: reduce');
});

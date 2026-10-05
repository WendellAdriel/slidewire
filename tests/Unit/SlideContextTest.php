<?php

declare(strict_types=1);

use Phiki\Theme\Theme;
use WendellAdriel\SlideWire\Support\SlideContext;

it('returns null presentation theme when no context is set', function (): void {
    $context = new SlideContext;

    expect($context->presentationTheme())->toBeNull()
        ->and($context->highlightTheme())->toBeNull();
});

it('returns deck theme as presentation theme', function (): void {
    $context = new SlideContext;
    $context->setDeck('black', null);

    expect($context->presentationTheme())->toBe('black');
});

it('returns deck highlight theme', function (): void {
    $context = new SlideContext;
    $context->setDeck('black', 'monokai');

    expect($context->highlightTheme())->toBe(Theme::Monokai);
});

it('returns slide theme overriding deck theme', function (): void {
    $context = new SlideContext;
    $context->setDeck('black', null);
    $context->setSlide('white');

    expect($context->presentationTheme())->toBe('white');
});

it('falls back to deck theme when slide theme is null', function (): void {
    $context = new SlideContext;
    $context->setDeck('black', null);
    $context->setSlide(null);

    expect($context->presentationTheme())->toBe('black');
});

it('clears deck context', function (): void {
    $context = new SlideContext;
    $context->setDeck('black', 'monokai');
    $context->clearDeck();

    expect($context->presentationTheme())->toBeNull()
        ->and($context->highlightTheme())->toBeNull();
});

it('clears slide context without affecting deck', function (): void {
    $context = new SlideContext;
    $context->setDeck('black', 'monokai');
    $context->setSlide('white');
    $context->clearSlide();

    expect($context->presentationTheme())->toBe('black')
        ->and($context->highlightTheme())->toBe(Theme::Monokai);
});

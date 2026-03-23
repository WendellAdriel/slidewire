<?php

declare(strict_types=1);

use WendellAdriel\SlideWire\Support\SlideContext;
use WendellAdriel\SlideWire\Support\UiThemeResolver;

it('resolves tokens from the active slide theme before the deck theme', function (): void {
    $context = app(SlideContext::class);
    $context->setDeck('black', null);
    $context->setSlide('white');

    $tokens = app(UiThemeResolver::class)->tokens();

    expect($tokens['surface'])->toContain('bg-white')
        ->and($tokens['heading_text'])->toContain('text-zinc-950');
});

it('falls back to the default theme for unknown values', function (): void {
    $resolver = app(UiThemeResolver::class);

    expect($resolver->theme('missing-theme'))->toBe('default')
        ->and($resolver->tokens('missing-theme')['body_text'])->toContain('text-zinc-100');
});

it('returns light theme detection for white and solarized themes', function (): void {
    $resolver = app(UiThemeResolver::class);

    expect($resolver->isLight('white'))->toBeTrue()
        ->and($resolver->isLight('solarized'))->toBeTrue()
        ->and($resolver->isLight('black'))->toBeFalse();
});

it('returns panel style maps for supported variants and tones', function (): void {
    $styles = app(UiThemeResolver::class)->panel('glass', 'warning', 'sunset');

    expect($styles['wrapper'])->toContain('rounded-[2rem]')
        ->and($styles['wrapper'])->toContain('backdrop-blur-xl')
        ->and($styles['wrapper'])->toContain('border-amber-300/24');
});

it('returns configured font styles for known fonts only', function (): void {
    $resolver = app(UiThemeResolver::class);

    expect($resolver->configuredFontFamily('Inter'))->toContain("'Inter'")
        ->and($resolver->configuredFontFamily('UnknownFont'))->toBeNull();
});

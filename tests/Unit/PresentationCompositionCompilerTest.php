<?php

declare(strict_types=1);

use WendellAdriel\SlideWire\Support\PresentationCompiler;

it('compiles blade partial includes inside a deck', function (): void {
    $compiled = app(PresentationCompiler::class)->compile('composed/includes');
    $slides = app(PresentationCompiler::class)->flattenSlides($compiled['slides']);

    expect($compiled['slides'])->toHaveCount(3)
        ->and($compiled['slides'][1])->toHaveCount(2)
        ->and($slides)->toHaveCount(4)
        ->and($slides[0]->html)->toContain('Included Intro')
        ->and($slides[1]->html)->toContain('Included Stack Top')
        ->and($slides[2]->html)->toContain('Included Stack Bottom')
        ->and($slides[3]->html)->toContain('Included Outro');
});

it('compiles directory-backed composed decks in lexicographic order', function (): void {
    $compiler = app(PresentationCompiler::class);
    $compiled = $compiler->compile('composed/product-launch');
    $slides = $compiler->flattenSlides($compiled['slides']);

    expect($compiled['deck_meta'])->toMatchArray([
        'theme' => 'black',
        'transition' => 'fade',
    ])
        ->and($slides)->toHaveCount(3)
        ->and($slides[0]->html)->toContain('Launch Intro')
        ->and($slides[1]->html)->toContain('Live Demo')
        ->and($slides[2]->html)->toContain('Roadmap')
        ->and($slides[0]->h)->toBe(0)
        ->and($slides[1]->h)->toBe(1)
        ->and($slides[2]->h)->toBe(2);
});

it('assigns stable unique ids to slides in composed decks', function (): void {
    $slides = app(PresentationCompiler::class)->flattenSlides(
        app(PresentationCompiler::class)->compile('composed/product-launch')['slides'],
    );

    $ids = array_map(fn (WendellAdriel\SlideWire\DTOs\Slide $slide): string => $slide->id, $slides);

    expect($ids)->toBe([
        'composed-product-launch-01-intro-0',
        'composed-product-launch-02-demo-0',
        'composed-product-launch-03-roadmap-0',
    ])
        ->and(count($ids))->toBe(count(array_unique($ids)));
});

it('rejects nested deck wrappers in composed presentation parts', function (): void {
    app(PresentationCompiler::class)->compile('composed/invalid-nested-deck');
})->throws(RuntimeException::class, 'cannot contain a deck wrapper');

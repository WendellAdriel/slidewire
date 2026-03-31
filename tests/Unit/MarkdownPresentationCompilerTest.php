<?php

declare(strict_types=1);

use WendellAdriel\SlideWire\Support\PresentationCompiler;

it('compiles a single-slide markdown deck into one slide', function (): void {
    $compiled = app(PresentationCompiler::class)->compile('markdown/single-slide');
    $slides = app(PresentationCompiler::class)->flattenSlides($compiled['slides']);

    expect($compiled['deck_meta'])->toBe([])
        ->and($slides)->toHaveCount(1)
        ->and($slides[0]->id)->toBe('markdown-single-slide-0')
        ->and($slides[0]->html)->toContain('<h1>Single Slide</h1>')
        ->and($slides[0]->html)->toContain('<li>One</li>');
});

it('compiles a multi-slide markdown deck with deck and slide metadata', function (): void {
    $compiler = app(PresentationCompiler::class);
    $compiled = $compiler->compile('markdown/standalone');
    $slides = $compiler->flattenSlides($compiled['slides']);

    expect($compiled['deck_meta'])->toMatchArray([
        'theme' => 'black',
        'transition' => 'fade',
        'highlight_theme' => 'catppuccin-mocha',
        'show_controls' => 'false',
    ])
        ->and($slides)->toHaveCount(2)
        ->and($slides[0]->html)->toContain('<h1>Intro</h1>')
        ->and($slides[0]->html)->toContain('<p>Welcome to SlideWire.</p>')
        ->and($slides[1]->meta)->toMatchArray([
            'theme' => 'white',
            'auto_slide' => '3000',
        ])
        ->and($slides[1]->html)->toContain('<h2>Code</h2>')
        ->and($slides[1]->html)->toContain('phiki');
});

it('ignores slide separators inside fenced code blocks', function (): void {
    $compiled = app(PresentationCompiler::class)->compile('markdown/code-fence-separator');
    $slides = app(PresentationCompiler::class)->flattenSlides($compiled['slides']);

    expect($slides)->toHaveCount(2)
        ->and($slides[0]->html)->toContain('language-html')
        ->and($slides[1]->html)->toContain('<h2>Actual Slide</h2>');
});

it('returns no slides for an empty markdown deck', function (): void {
    $compiled = app(PresentationCompiler::class)->compile('markdown/standalone-empty');

    expect($compiled['deck_meta'])->toMatchArray(['theme' => 'black'])
        ->and($compiled['slides'])->toBe([]);
});

it('rejects malformed markdown frontmatter with a clear exception', function (): void {
    app(PresentationCompiler::class)->compile('markdown/standalone-invalid-frontmatter');
})->throws(RuntimeException::class, 'Malformed deck frontmatter');

it('rejects unsupported non-scalar markdown frontmatter values', function (): void {
    app(PresentationCompiler::class)->compile('markdown/standalone-invalid-non-scalar');
})->throws(RuntimeException::class, 'Unsupported non-scalar deck frontmatter value');

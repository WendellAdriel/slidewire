<?php

declare(strict_types=1);

use WendellAdriel\SlideWire\Support\PresentationPathResolver;

it('throws exception for empty presentation name', function (): void {
    app(PresentationPathResolver::class)->presentationPath('');
})->throws(InvalidArgumentException::class, 'cannot be empty');

it('throws exception for presentation name that normalizes to empty', function (): void {
    app(PresentationPathResolver::class)->presentationPath('....');
})->throws(InvalidArgumentException::class, 'cannot be empty');

it('sanitizes double-dot path traversal attempts', function (): void {
    $resolver = app(PresentationPathResolver::class);

    expect($resolver->presentationPath('../../etc/passwd'))->toBeNull();
    expect($resolver->presentationPath('....//etc/passwd'))->toBeNull();
});

it('returns null for non-existent presentations', function (): void {
    $resolver = app(PresentationPathResolver::class);

    expect($resolver->presentationPath('non-existent-deck'))->toBeNull();
});

it('resolves markdown presentations when no blade file exists', function (): void {
    $resolver = app(PresentationPathResolver::class);

    expect($resolver->presentationPath('markdown/standalone'))
        ->toBeString()
        ->toEndWith('markdown/standalone.md');
});

it('prefers blade presentations over markdown files with the same name', function (): void {
    $resolver = app(PresentationPathResolver::class);

    expect($resolver->presentationPath('markdown/standalone-blade-precedence'))
        ->toBeString()
        ->toEndWith('markdown/standalone-blade-precedence.blade.php');
});

it('resolves nested markdown presentation paths', function (): void {
    $resolver = app(PresentationPathResolver::class);

    expect($resolver->presentationPath('markdown/nested/showcase'))
        ->toBeString()
        ->toEndWith('markdown/nested/showcase.md');
});

it('resolves presentation directories after checking file-based sources', function (): void {
    $resolver = app(PresentationPathResolver::class);

    expect($resolver->presentationPath('composed/product-launch'))
        ->toBeString()
        ->toEndWith('composed/product-launch');
});

it('resolves nested presentation paths', function (): void {
    $resolver = app(PresentationPathResolver::class);
    $path = $resolver->presentationPath('team/q1-kickoff');

    expect($path)->toBeString()
        ->and($path)->toEndWith('team/q1-kickoff.blade.php');
});

it('normalizes backslashes to forward slashes', function (): void {
    $resolver = app(PresentationPathResolver::class);

    $path = $resolver->absolutePresentationPath('team\\q1-kickoff');

    expect($path)->toContain('team/q1-kickoff.blade.php');
});

it('strips leading and trailing slashes from presentation names', function (): void {
    $resolver = app(PresentationPathResolver::class);

    $path1 = $resolver->absolutePresentationPath('/demo/');
    $path2 = $resolver->absolutePresentationPath('demo');

    expect($path1)->toBe($path2);
});

it('returns presentation directory for existing presentations', function (): void {
    $resolver = app(PresentationPathResolver::class);
    $dir = $resolver->presentationDirectory('demo');

    expect($dir)->toBeString()
        ->and($dir)->toEndWith('slides');
});

it('returns null directory for non-existent presentations', function (): void {
    $resolver = app(PresentationPathResolver::class);

    expect($resolver->presentationDirectory('nonexistent'))->toBeNull();
});

it('returns first configured root', function (): void {
    $resolver = app(PresentationPathResolver::class);

    expect($resolver->firstRoot())->toBeString()
        ->and($resolver->firstRoot())->toContain('slides');
});

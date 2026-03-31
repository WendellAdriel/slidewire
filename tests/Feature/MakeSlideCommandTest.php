<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('generates a presentation scaffold', function (): void {
    $root = slidewireScaffoldRoot();
    $target = $root . '/team/q1-kickoff.blade.php';

    File::deleteDirectory($root . '/team');

    Artisan::call('make:slidewire', [
        'name' => 'team/q1-kickoff',
        '--title' => 'Q1 Kickoff',
    ]);

    $contents = File::get($target);

    expect(File::exists($target))->toBeTrue()
        ->and($contents)->toContain('Q1 Kickoff')
        ->and($contents)->toContain('use Livewire\\Component;')
        ->and($contents)->toContain('new class extends Component');
});

it('generates a standalone markdown presentation scaffold', function (): void {
    $root = slidewireScaffoldRoot();
    $target = $root . '/team/markdown-kickoff.md';

    File::deleteDirectory($root . '/team');

    Artisan::call('make:slidewire', [
        'name' => 'team/markdown-kickoff',
        '--title' => 'Markdown Kickoff',
        '--md' => true,
    ]);

    $contents = File::get($target);

    expect(File::exists($target))->toBeTrue()
        ->and($contents)->toContain('# Markdown Kickoff')
        ->and($contents)->toContain('<!-- slide -->')
        ->and($contents)->toContain("Route::slidewire('/slides/demo', 'team/markdown-kickoff');");
});

it('generates a multi-file presentation scaffold', function (): void {
    $root = slidewireScaffoldRoot();
    $target = $root . '/team/multi-kickoff';

    File::deleteDirectory($root . '/team');

    Artisan::call('make:slidewire', [
        'name' => 'team/multi-kickoff',
        '--title' => 'Multi Kickoff',
        '--multi' => true,
    ]);

    expect(File::isDirectory($target))->toBeTrue()
        ->and(File::exists($target . '/deck.blade.php'))->toBeTrue()
        ->and(File::exists($target . '/01-intro.blade.php'))->toBeTrue()
        ->and(File::exists($target . '/02-markdown.md'))->toBeTrue()
        ->and(File::get($target . '/01-intro.blade.php'))->toContain('Multi Kickoff')
        ->and(File::get($target . '/02-markdown.md'))->toContain('## Markdown Slide');
});

it('rejects combining markdown and multi-file scaffolding options', function (): void {
    slidewireScaffoldRoot();

    $exitCode = Artisan::call('make:slidewire', [
        'name' => 'team/invalid-kickoff',
        '--md' => true,
        '--multi' => true,
    ]);

    expect($exitCode)->toBe(1)
        ->and(Artisan::output())->toContain('cannot be combined');
});

function slidewireScaffoldRoot(): string
{
    $root = sys_get_temp_dir() . '/slidewire-make-command-tests';

    File::deleteDirectory($root);
    File::ensureDirectoryExists($root);

    config()->set('slidewire.presentation_roots', [$root]);

    return $root;
}

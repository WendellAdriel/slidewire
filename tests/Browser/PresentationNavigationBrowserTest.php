<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

function slidewireActiveFrameMetrics($page): array
{
    return json_decode((string) $page->script(<<<'JS_WRAP'
        (() => {
            const frame = document.querySelector('.slidewire-frame.is-active');
            const content = frame?.querySelector('.slidewire-content');
            const frameRect = frame.getBoundingClientRect();
            const contentRect = content.getBoundingClientRect();
    
            return JSON.stringify({
                frameLeft: frameRect.left,
                frameRight: frameRect.right,
                viewportWidth: window.innerWidth,
                contentCenter: contentRect.left + (contentRect.width / 2),
                viewportCenter: window.innerWidth / 2,
                animationCount: frame.getAnimations().length,
                transform: getComputedStyle(frame).transform,
                opacity: getComputedStyle(frame).opacity,
            });
        })()
    JS_WRAP), true);
}

function expectSlidewireFrameAligned(array $metrics): void
{
    expect(abs($metrics['frameLeft']))->toBeLessThanOrEqual(2)
        ->and(abs($metrics['frameRight'] - $metrics['viewportWidth']))->toBeLessThanOrEqual(2)
        ->and(abs($metrics['contentCenter'] - $metrics['viewportCenter']))->toBeLessThanOrEqual(4)
        ->and($metrics['animationCount'])->toBe(0)
        ->and($metrics['transform'])->toBe('none')
        ->and($metrics['opacity'])->toBe('1');
}

it('navigates a presentation in the browser', function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);

    Route::slidewire('/slides/demo', 'demo');

    $page = visit('/slides/demo');

    $page->waitForText('Demo Intro')
        ->assertSee('Demo Intro')
        ->assertNoJavaScriptErrors();
})->group('browser');

it('renders a vertical-slide presentation in the browser', function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);

    Route::slidewire('/slides/vertical', 'vertical');

    $page = visit('/slides/vertical');

    $page->waitForText('Horizontal Slide 1')
        ->assertSee('Horizontal Slide 1')
        ->assertNoJavaScriptErrors();
})->group('browser');

it('keeps the active frame aligned after repeated back and forward navigation', function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);

    Route::slidewire('/slides/offset-regression', 'offset-regression');

    $page = visit('/slides/offset-regression');

    $page->resize(1624, 978)
        ->waitForText('Offset Intro')
        ->assertNoJavaScriptErrors();

    expectSlidewireFrameAligned(slidewireActiveFrameMetrics($page));

    foreach (range(1, 3) as $_) {
        $page->script("document.querySelector('.slidewire-control-right').click()");
        $page->wait(0.45);
    }

    $page->assertSee('Zoom Cards');
    expectSlidewireFrameAligned(slidewireActiveFrameMetrics($page));

    $page->script("document.querySelector('.slidewire-control-left').click()");
    $page->wait(0.45);

    $page->assertSee('Fade Metrics');
    expectSlidewireFrameAligned(slidewireActiveFrameMetrics($page));

    $page->script("document.querySelector('.slidewire-control-right').click()");
    $page->wait(0.45);

    $page->assertSee('Zoom Cards')
        ->assertNoJavaScriptErrors();

    expectSlidewireFrameAligned(slidewireActiveFrameMetrics($page));
})->group('browser');

it('keeps the active frame aligned after interrupted back and forward navigation', function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);

    Route::slidewire('/slides/offset-regression-interrupted', 'offset-regression');

    $page = visit('/slides/offset-regression-interrupted');

    $page->resize(1624, 978)
        ->waitForText('Offset Intro')
        ->assertNoJavaScriptErrors();

    $page->script("document.querySelector('.slidewire-control-right').click()");
    $page->wait(0.06);
    $page->script("document.querySelector('.slidewire-control-right').click()");
    $page->wait(0.06);
    $page->script("document.querySelector('.slidewire-control-left').click()");
    $page->wait(0.06);
    $page->script("document.querySelector('.slidewire-control-right').click()");
    $page->wait(0.55);

    $page->assertSee('Fade Metrics')
        ->assertNoJavaScriptErrors();

    expectSlidewireFrameAligned(slidewireActiveFrameMetrics($page));
})->group('browser');

it('keeps the active frame aligned after vertical navigation', function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);

    Route::slidewire('/slides/offset-regression-vertical', 'offset-regression');

    $page = visit('/slides/offset-regression-vertical');

    $page->resize(1624, 978)
        ->waitForText('Offset Intro')
        ->assertNoJavaScriptErrors();

    foreach (range(1, 4) as $_) {
        $page->script("document.querySelector('.slidewire-control-right').click()");
        $page->wait(0.45);
    }

    $page->assertSee('Vertical Overview');
    expectSlidewireFrameAligned(slidewireActiveFrameMetrics($page));

    $page->script("document.querySelector('.slidewire-control-down').click()");
    $page->wait(0.45);

    $page->assertSee('Vertical Detail')
        ->assertNoJavaScriptErrors();

    expectSlidewireFrameAligned(slidewireActiveFrameMetrics($page));
})->group('browser');

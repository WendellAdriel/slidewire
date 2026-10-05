<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use WendellAdriel\SlideWire\DTOs\RemoteConfig;
use WendellAdriel\SlideWire\Support\RemoteSessionManager;

function slidewireActiveHeading($page): string
{
    return trim((string) $page->script(
        "(() => document.querySelector('.slidewire-frame.is-active .slidewire-content h1')?.textContent ?? '')()"
    ));
}

/** @return array{controller: string, viewer: string} */
function remotePaths(string $key, string $presentation = 'remote'): array
{
    $controllerUrl = URL::temporarySignedRoute('slidewire.' . $presentation, now()->addHour(), ['remote' => $key]);
    $parts = parse_url($controllerUrl);

    return [
        'controller' => $parts['path'] . '?' . $parts['query'],
        'viewer' => "/slides/{$presentation}?remote=" . $key,
    ];
}

beforeEach(function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);
    // A file store is process-independent, so state seeded in the test process is
    // visible to the served browser requests regardless of how Pest serves them.
    config()->set('slidewire.remote', new RemoteConfig(pollInterval: '1s', cacheStore: 'file'));
    Cache::store('file')->clear();

    Route::slidewire('/slides/remote', 'remote');
    Route::slidewire('/slides/remote-fragments', 'remote-fragments');
    Route::getRoutes()->refreshNameLookups();
});

it('syncs a viewer DOM to controller navigation across two browser contexts', function (): void {
    $session = app(RemoteSessionManager::class)->create('remote', '1h');
    $paths = remotePaths($session['key']);

    $controller = visit($paths['controller']);
    $viewer = visit($paths['viewer']);

    $controller->waitForText('Remote Slide One')->assertNoJavaScriptErrors();
    $viewer->waitForText('Remote Slide One')->assertNoJavaScriptErrors();

    expect(slidewireActiveHeading($viewer))->toBe('Remote Slide One');

    // Controller advances one slide.
    $controller->script("document.querySelector('.slidewire-control-right').click()");
    $controller->wait(0.6);
    expect(slidewireActiveHeading($controller))->toBe('Remote Slide Two');

    // Viewer follows within a poll cycle (1s).
    $viewer->wait(1.8);
    expect(slidewireActiveHeading($viewer))->toBe('Remote Slide Two');

    $viewer->assertNoJavaScriptErrors();
})->group('browser');

it('preserves revealed viewer fragments and stops polling when a session ends or expires', function (string $ending): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('remote-fragments', '1h');
    $paths = remotePaths($session['key'], 'remote-fragments');
    $controller = visit($paths['controller']);
    $viewer = visit($paths['viewer']);
    $controller->waitForText('Fragment Deck')->assertNoJavaScriptErrors();
    $viewer->waitForText('Fragment Deck')->assertNoJavaScriptErrors();

    $controller->script("document.querySelector('.slidewire-control-right').click()");
    $viewer->wait(1.8);

    $viewer->assertScript("document.querySelectorAll('.slidewire-frame.is-active .slidewire-fragment-visible').length", 1);

    if ($ending === 'deleted') {
        $manager->delete($session['key']);
    } else {
        $cacheKey = "slidewire:remote:{$session['key']}";
        $state = Cache::store('file')->get($cacheKey);
        $state['expires_at'] = now()->subSecond()->timestamp;
        Cache::store('file')->put($cacheKey, $state, 60);
    }

    $viewer->wait(1.8)
        ->assertScript("document.querySelectorAll('.slidewire-frame.is-active .slidewire-fragment-visible').length", 1)
        ->assertScript("document.querySelector('[x-ref=deckRoot]').getAttributeNames().some(name => name.startsWith('wire:poll'))", false)
        ->assertNoJavaScriptErrors();
})->with(['deleted', 'expired'])->group('browser');

it('keeps a passive viewer from self-navigating', function (): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('remote', '1h');
    $manager->update($session['key'], 0, -1, false); // viewer_controls off

    $paths = remotePaths($session['key']);
    $viewer = visit($paths['viewer']);

    $viewer->waitForText('Remote Slide One')->assertNoJavaScriptErrors();
    expect(slidewireActiveHeading($viewer))->toBe('Remote Slide One');

    // Attempt to self-navigate; the passive guard must ignore it.
    $viewer->script("document.querySelector('.slidewire-control-right')?.click()");
    $viewer->wait(0.6);

    expect(slidewireActiveHeading($viewer))->toBe('Remote Slide One');
    $viewer->assertNoJavaScriptErrors();
})->group('browser');

it('returns a free browsing viewer to the presenter when the lock is restored', function (): void {
    $session = app(RemoteSessionManager::class)->create('remote', '1h');
    $paths = remotePaths($session['key']);
    $controller = visit($paths['controller']);
    $viewer = visit($paths['viewer']);
    $controller->waitForText('Remote Slide One')->assertNoJavaScriptErrors();
    $viewer->waitForText('Remote Slide One')->assertNoJavaScriptErrors();

    $controller->script("document.querySelector('.slidewire-remote-toggle').click()");
    $viewer->wait(1.8);
    $viewer->script("document.querySelector('.slidewire-control-right').click()");
    $viewer->wait(0.6);
    expect(slidewireActiveHeading($viewer))->toBe('Remote Slide Two');
    expect(slidewireActiveHeading($controller))->toBe('Remote Slide One');

    $controller->script("document.querySelector('.slidewire-remote-toggle').click()");
    $viewer->wait(1.8);

    expect(slidewireActiveHeading($viewer))->toBe('Remote Slide One');
    $viewer->assertNoJavaScriptErrors();
})->group('browser');

it('keeps revealed fragments visible when the controller toggles the lock', function (): void {
    $session = app(RemoteSessionManager::class)->create('remote-fragments', '1h');
    $paths = remotePaths($session['key'], 'remote-fragments');

    $controller = visit($paths['controller']);
    $controller->waitForText('Fragment Deck')->assertNoJavaScriptErrors();

    $fragmentShown = fn (): string => (string) $controller->script(
        "(() => document.querySelector('.slidewire-frame.is-active .slidewire-fragment-visible') ? 'yes' : 'no')()"
    );

    // Reveal the fragment.
    $controller->script("document.querySelector('.slidewire-control-right').click()");
    $controller->wait(0.6);
    expect($fragmentShown())->toBe('yes');

    // Toggling the lock must not re-render/morph and wipe the revealed fragment.
    $controller->script("document.querySelector('.slidewire-remote-toggle').click()");
    $controller->wait(0.8);
    expect($fragmentShown())->toBe('yes');

    $controller->assertNoJavaScriptErrors();
})->group('browser');

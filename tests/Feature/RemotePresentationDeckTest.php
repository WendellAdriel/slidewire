<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use WendellAdriel\SlideWire\Livewire\PresentationDeck;
use WendellAdriel\SlideWire\Support\RemoteSessionManager;

beforeEach(function (): void {
    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);
    Route::slidewire('/slides/demo', 'demo');
    // Runtime-registered routes leave the name lookup stale; refreshing it mirrors
    // a real app where route files load through the flow that populates the lookup.
    Route::getRoutes()->refreshNameLookups();
});

/** @return array<string, string> */
function signedRemoteQuery(string $key): array
{
    $signedUrl = URL::temporarySignedRoute('slidewire.demo', now()->addHour(), ['remote' => $key]);
    $query = [];
    parse_str((string) parse_url($signedUrl, PHP_URL_QUERY), $query);

    return $query;
}

it('mounts in solo mode without a remote param', function (): void {
    Livewire::test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSet('remoteMode', 'solo')
        ->assertSet('remoteSessionKey', null);
});

it('mounts in controller mode with a valid signed URL', function (): void {
    $session = app(RemoteSessionManager::class)->create('demo', '1h');

    Livewire::withQueryParams(signedRemoteQuery($session['key']))
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSet('remoteMode', 'controller')
        ->assertSet('remoteSessionKey', $session['key']);
});

it('resumes a controller from the cached position without resetting it', function (): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('demo', '1h');
    $manager->update($session['key'], 2, -1, true);

    Livewire::withQueryParams(signedRemoteQuery($session['key']))
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSet('remoteMode', 'controller')
        ->assertSet('activeIndex', 2);

    // The initial dehydrate must not reset the live session back to slide 0.
    expect($manager->get($session['key'])->index)->toBe(2);
});

it('honors an explicit start slide over the cached controller position', function (): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('demo', '1h');
    $manager->update($session['key'], 2, -1, true);

    Livewire::withQueryParams(signedRemoteQuery($session['key']))
        ->test(PresentationDeck::class, ['presentation' => 'demo', 'startSlide' => 1])
        ->assertSet('remoteMode', 'controller')
        ->assertSet('activeIndex', 1);
});

it('mounts in viewer mode with a remote param but no signature', function (): void {
    $session = app(RemoteSessionManager::class)->create('demo', '1h');

    Livewire::withQueryParams(['remote' => $session['key']])
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSet('remoteMode', 'viewer')
        ->assertSet('remoteSessionKey', $session['key']);
});

it('falls back to solo when the session does not exist', function (): void {
    Livewire::withQueryParams(['remote' => 'nonexistent'])
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSet('remoteMode', 'solo')
        ->assertSet('remoteSessionKey', null);
});

it('falls back to solo when the session is for a different presentation', function (): void {
    $session = app(RemoteSessionManager::class)->create('other', '1h');

    Livewire::withQueryParams(['remote' => $session['key']])
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSet('remoteMode', 'solo')
        ->assertSet('remoteSessionKey', null);
});

it('falls back to viewer mode with an expired signature', function (): void {
    $session = app(RemoteSessionManager::class)->create('demo', '1h');

    $signedUrl = URL::temporarySignedRoute('slidewire.demo', now()->subMinute(), ['remote' => $session['key']]);
    $query = [];
    parse_str((string) parse_url($signedUrl, PHP_URL_QUERY), $query);

    Livewire::withQueryParams($query)
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSet('remoteMode', 'viewer');
});

it('syncs the viewer to the controller position on mount', function (): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('demo', '1h');
    $manager->update($session['key'], 1, -1, true);

    Livewire::withQueryParams(['remote' => $session['key']])
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSet('activeIndex', 1);
});

it('writes state to cache on controller dehydrate', function (): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('demo', '1h');

    Livewire::withQueryParams(signedRemoteQuery($session['key']))
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->call('nextSlide');

    $state = $manager->get($session['key']);

    // demo slide 0 has one fragment, so a single nextSlide reveals it in place.
    expect($state->index)->toBe(0)
        ->and($state->fragment)->toBe(0);
});

it('does not write state to cache in viewer mode', function (): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('demo', '1h');

    Livewire::withQueryParams(['remote' => $session['key']])
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->call('goToSlide', 2);

    expect($manager->get($session['key'])->index)->toBe(0);
});

it('lets the controller toggle viewer controls and persists it', function (): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('demo', '1h');

    Livewire::withQueryParams(signedRemoteQuery($session['key']))
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSet('viewerControls', false)
        ->call('toggleViewerControls')
        ->assertSet('viewerControls', true);

    expect($manager->get($session['key'])->viewerControls)->toBeTrue();
});

it('lets the controller end the session and return to solo', function (): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('demo', '1h');

    Livewire::withQueryParams(signedRemoteQuery($session['key']))
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->call('endRemoteSession')
        ->assertSet('remoteMode', 'solo')
        ->assertSet('remoteSessionKey', null);

    expect($manager->exists($session['key']))->toBeFalse();
});

it('uses the session poll interval in viewer mode', function (): void {
    $session = app(RemoteSessionManager::class)->create('demo', '1h', '3s');

    Livewire::withQueryParams(['remote' => $session['key']])
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSet('pollInterval', '3s');
});

it('syncs viewer state from cache via poll', function (): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('demo', '1h');
    $manager->update($session['key'], 1, -1, true);

    Livewire::withQueryParams(['remote' => $session['key']])
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->call('pollRemoteState')
        ->assertSet('activeIndex', 1)
        ->assertSet('activeFragment', -1);
});

it('degrades to solo when the session expires during viewing', function (): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('demo', '1h');

    $component = Livewire::withQueryParams(['remote' => $session['key']])
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSet('remoteMode', 'viewer');

    $manager->delete($session['key']);

    $component->call('pollRemoteState')
        ->assertSet('remoteMode', 'solo')
        ->assertSet('remoteSessionKey', null);
});

it('only re-syncs a free-browsing viewer on a controller delta', function (): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('demo', '1h');
    $manager->update($session['key'], 0, -1, true); // free-browse enabled

    $component = Livewire::withQueryParams(['remote' => $session['key']])
        ->test(PresentationDeck::class, ['presentation' => 'demo']);

    // Poll with no change leaves position at 0.
    $component->call('pollRemoteState')->assertSet('activeIndex', 0);

    // Viewer free-browses to slide 1; a no-delta poll must NOT snap it back.
    $component->call('goToSlide', 1)->assertSet('activeIndex', 1);
    $component->call('pollRemoteState')->assertSet('activeIndex', 1);

    // Controller moves; the next poll snaps the viewer to the new position.
    $manager->update($session['key'], 2, -1, true);
    $component->call('pollRemoteState')->assertSet('activeIndex', 2);
});

it('blocks server-side navigation for a passive viewer', function (): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('demo', '1h');
    $manager->update($session['key'], 0, -1, false); // viewer controls off

    Livewire::withQueryParams(['remote' => $session['key']])
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSet('viewerControls', false)
        ->call('nextSlide')
        ->assertSet('activeIndex', 0)
        ->assertSet('activeFragment', -1);
});

it('allows server-side navigation for a free-browse viewer', function (): void {
    $manager = app(RemoteSessionManager::class);
    $session = $manager->create('demo', '1h');
    $manager->update($session['key'], 0, -1, true); // viewer controls on

    Livewire::withQueryParams(['remote' => $session['key']])
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSet('viewerControls', true)
        ->call('goToSlide', 2)
        ->assertSet('activeIndex', 2);
});

it('renders no remote markup in solo mode', function (): void {
    // Needles target mode-specific markup (wire: directives), not the CSS class
    // names, which always appear in the <style> block regardless of mode.
    Livewire::test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertDontSee('wire:poll')
        ->assertDontSee('toggleViewerControls')
        ->assertDontSee('endRemoteSession');
});

it('renders the poll directive in viewer mode', function (): void {
    $session = app(RemoteSessionManager::class)->create('demo', '1h');

    Livewire::withQueryParams(['remote' => $session['key']])
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSee('wire:poll')
        ->assertDontSee('toggleViewerControls');
});

it('renders the controller UI in controller mode', function (): void {
    $session = app(RemoteSessionManager::class)->create('demo', '1h');

    Livewire::withQueryParams(signedRemoteQuery($session['key']))
        ->test(PresentationDeck::class, ['presentation' => 'demo'])
        ->assertSee('toggleViewerControls')
        ->assertSee('endRemoteSession')
        ->assertDontSee('wire:poll');
});

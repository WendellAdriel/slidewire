<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('renders the ui components fixture in the browser without javascript errors', function (): void {
    if (! class_exists(Pest\Browser\Plugin::class)) {
        test()->markTestSkipped('Browser plugin is not installed in this environment.');
    }

    config()->set('slidewire.presentation_roots', [__DIR__ . '/../fixtures/views/pages/slides']);

    Route::slidewire('/slides/ui-components', 'ui-components');

    visit('/slides/ui-components')
        ->waitForText('SlideWire Launch Kit')
        ->assertSee('SlideWire Launch Kit')
        ->assertSee('A first-party set of polished presentation components for modern Blade decks.')
        ->assertNoJavaScriptErrors();
})->group('browser');

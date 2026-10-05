<?php

declare(strict_types=1);

use Phiki\Theme\Theme;
use WendellAdriel\SlideWire\DTOs\FontConfig;
use WendellAdriel\SlideWire\DTOs\HighlightConfig;
use WendellAdriel\SlideWire\DTOs\RemoteConfig;
use WendellAdriel\SlideWire\DTOs\SlidesConfig;
use WendellAdriel\SlideWire\DTOs\ThemeConfig;
use WendellAdriel\SlideWire\DTOs\ThemeFont;
use WendellAdriel\SlideWire\Enums\FontSource;
use WendellAdriel\SlideWire\Enums\SlideTransition;
use WendellAdriel\SlideWire\Enums\SlideTransitionSpeed;

return [

    /*
    |--------------------------------------------------------------------------
    | Presentation Roots
    |--------------------------------------------------------------------------
    |
    | Here you may configure the directories where SlideWire should look for
    | Blade and Markdown presentations. These paths are used by the compiler,
    | route macro, and scaffold command when resolving presentation files.
    |
    */

    'presentation_roots' => [
        resource_path('views/pages/slides'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Slide Defaults
    |--------------------------------------------------------------------------
    |
    | These values provide the fallback runtime settings for every deck and
    | slide. Slide attributes take priority over deck attributes, and deck
    | attributes take priority over the values defined here.
    |
    */

    'slides' => new SlidesConfig(
        theme: 'default',
        showControls: true,
        showProgress: true,
        showFullscreenButton: true,
        keyboard: true,
        touch: true,
        transition: SlideTransition::Slide,
        transitionDuration: 350,
        transitionSpeed: SlideTransitionSpeed::Default,
        autoSlide: 0,
        autoSlidePauseOnInteraction: true,
        highlight: new HighlightConfig(
            enabled: true,
            theme: Theme::CatppuccinMocha,
            font: 'JetBrainsMono',
            fontSize: 'text-base',
        ),
    ),

    /*
    |--------------------------------------------------------------------------
    | Theme Presets
    |--------------------------------------------------------------------------
    |
    | Each theme preset defines the background classes, syntax highlight theme,
    | and typography applied when a deck or slide uses that theme name. The
    | config uses DTOs so the structure stays explicit and strongly typed.
    |
    */

    'themes' => [
        'default' => new ThemeConfig(
            background: 'bg-gradient-to-br from-slate-900 via-blue-950 to-slate-950 text-slate-50',
            highlightTheme: Theme::CatppuccinMocha,
            title: new ThemeFont(font: 'Inter', color: 'text-slate-50', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-slate-200', size: 'text-lg'),
        ),
        'black' => new ThemeConfig(
            background: 'bg-slate-900 text-slate-200',
            highlightTheme: Theme::CatppuccinMocha,
            title: new ThemeFont(font: 'Inter', color: 'text-slate-200', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-slate-300', size: 'text-lg'),
        ),
        'white' => new ThemeConfig(
            background: 'bg-white text-zinc-800',
            highlightTheme: Theme::CatppuccinLatte,
            title: new ThemeFont(font: 'Inter', color: 'text-zinc-800', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-zinc-600', size: 'text-lg'),
        ),
        'aurora' => new ThemeConfig(
            background: 'bg-gradient-to-br from-emerald-950 via-cyan-900 to-slate-950 text-emerald-50',
            highlightTheme: Theme::CatppuccinMocha,
            title: new ThemeFont(font: 'Inter', color: 'text-emerald-50', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-cyan-100', size: 'text-lg'),
        ),
        'sunset' => new ThemeConfig(
            background: 'bg-gradient-to-br from-rose-950 via-orange-900 to-amber-700 text-orange-50',
            highlightTheme: Theme::CatppuccinMocha,
            title: new ThemeFont(font: 'Inter', color: 'text-orange-50', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-amber-100', size: 'text-lg'),
        ),
        'neon' => new ThemeConfig(
            background: 'bg-gradient-to-br from-fuchsia-950 via-violet-900 to-cyan-900 text-fuchsia-50',
            highlightTheme: Theme::CatppuccinMocha,
            title: new ThemeFont(font: 'Inter', color: 'text-fuchsia-50', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-cyan-100', size: 'text-lg'),
        ),
        'solarized' => new ThemeConfig(
            background: 'bg-yellow-50 text-slate-600',
            highlightTheme: Theme::CatppuccinLatte,
            title: new ThemeFont(font: 'Inter', color: 'text-slate-700', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-slate-600', size: 'text-lg'),
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Font Loading
    |--------------------------------------------------------------------------
    |
    | Map font family names used by theme typography and code blocks to their
    | loading strategy. System fonts require no loading, while Google Fonts
    | families are automatically included in the rendered presentation.
    |
    */

    'fonts' => [
        'Inter' => new FontConfig(source: FontSource::Google, weights: [400, 600, 700]),
        'JetBrainsMono' => new FontConfig(source: FontSource::Google, weights: [400, 700]),
    ],

    /*
    |--------------------------------------------------------------------------
    | Remote Presenter Control
    |--------------------------------------------------------------------------
    |
    | Settings for the optional remote control feature. A deck only enters
    | remote mode when a `remote` query parameter is present; otherwise these
    | values are unused and the deck renders exactly as a solo presentation.
    | Pass `cacheStore` to the DTO to isolate remote state on a specific cache
    | store (e.g. 'redis'); it defaults to the application's default store.
    |
    */

    'remote' => new RemoteConfig(
        ttl: '2h',             // default session TTL when --ttl is omitted
        pollInterval: '2s',    // viewer wire:poll cadence (overridable per session with --poll)
        viewerControls: false, // viewers are locked to the presenter by default; the controller can allow free browsing live
    ),
];

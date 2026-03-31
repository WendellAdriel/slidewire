<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use WendellAdriel\SlideWire\Commands\MakeSlideCommand;
use WendellAdriel\SlideWire\Support\CodeBlockPrecompiler;
use WendellAdriel\SlideWire\Support\CodeHighlighter;
use WendellAdriel\SlideWire\Support\EffectiveSettingsResolver;
use WendellAdriel\SlideWire\Support\MarkdownPresentationParser;
use WendellAdriel\SlideWire\Support\MarkdownRenderer;
use WendellAdriel\SlideWire\Support\PresentationCompiler;
use WendellAdriel\SlideWire\Support\PresentationPathResolver;
use WendellAdriel\SlideWire\Support\SlideContext;
use WendellAdriel\SlideWire\Support\SlideIdGenerator;
use WendellAdriel\SlideWire\Support\ThemeResolver;
use WendellAdriel\SlideWire\Support\UiThemeResolver;

class SlideWireServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/slidewire.php', 'slidewire');

        $this->app->singleton(PresentationPathResolver::class);
        $this->app->singleton(CodeHighlighter::class);
        $this->app->singleton(MarkdownRenderer::class);
        $this->app->singleton(MarkdownPresentationParser::class);
        $this->app->singleton(SlideIdGenerator::class);
        $this->app->singleton(PresentationCompiler::class);
        $this->app->singleton(EffectiveSettingsResolver::class);
        $this->app->singleton(ThemeResolver::class);
        $this->app->singleton(SlideContext::class);
        $this->app->singleton(UiThemeResolver::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'slidewire');

        Blade::componentNamespace('WendellAdriel\\SlideWire\\View\\Components', 'slidewire');
        Blade::prepareStringsForCompilationUsing(new CodeBlockPrecompiler());

        if (! Route::hasMacro('slidewire')) {
            Route::macro('slidewire', fn (string $uri, string $presentation): \Illuminate\Routing\Route => Route::livewire($uri, 'slidewire::presentation-deck')
                ->name('slidewire.' . str_replace('/', '.', trim($presentation, '/')))
                ->defaults('presentation', trim($presentation, '/')));
        }

        Livewire::addNamespace(
            namespace: 'slidewire',
            viewPath: __DIR__ . '/../resources/views/livewire',
            classNamespace: 'WendellAdriel\\SlideWire\\Livewire',
            classPath: __DIR__ . '/Livewire',
            classViewPath: __DIR__ . '/../resources/views/livewire',
        );

        $this->publishes([
            __DIR__ . '/../config/slidewire.php' => config_path('slidewire.php'),
        ], 'slidewire-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/slidewire'),
        ], 'slidewire-views');

        $this->publishes([
            __DIR__ . '/../stubs' => base_path('stubs/slidewire'),
        ], 'slidewire-stubs');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeSlideCommand::class,
            ]);
        }
    }
}

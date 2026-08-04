<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use WendellAdriel\SlideWire\DTOs\Slide;
use WendellAdriel\SlideWire\DTOs\SlidesConfig;
use WendellAdriel\SlideWire\Support\EffectiveSettingsResolver;
use WendellAdriel\SlideWire\Support\PresentationCompiler;
use WendellAdriel\SlideWire\Support\RemoteSessionManager;
use WendellAdriel\SlideWire\Support\SlideViewDataFactory;
use WendellAdriel\SlideWire\Support\ThemeResolver;

#[Layout('slidewire::layouts.blank')]
class PresentationDeck extends Component
{
    #[Locked]
    public string $presentation = 'index';

    /** @var array<int, array<int, Slide>> */
    public array $columns = [];

    /** @var array<int, Slide> */
    public array $slides = [];

    /** @var array<string, string> */
    public array $deckMeta = [];

    /** @var array<int, int> */
    public array $gridShape = [];

    public int $activeIndex = 0;

    public int $activeFragment = -1;

    #[Locked]
    public string $remoteMode = 'solo';

    #[Locked]
    public ?string $remoteSessionKey = null;

    #[Locked]
    public string $pollInterval = '2s';

    #[Locked]
    public bool $viewerControls = false;

    #[Locked]
    public int $lastControllerIndex = -1;

    #[Locked]
    public int $lastControllerFragment = -1;

    public function mount(string $presentation = 'index', ?int $startSlide = null): void
    {
        $this->presentation = trim($presentation, '/');
        $compiler = app(PresentationCompiler::class);
        $compiled = $compiler->compile($this->presentation);
        $this->deckMeta = $compiled['deck_meta'];
        $this->columns = $compiled['slides'];
        $this->slides = $compiler->flattenSlides($this->columns);
        $this->gridShape = array_map(count(...), $this->columns);

        abort_if($this->slides === [], 404, "SlideWire presentation [{$this->presentation}] was not found.");

        if ($startSlide !== null) {
            $this->activeIndex = $this->normalizeIndex($startSlide);
        }

        $this->initializeRemoteSession($startSlide);
    }

    public function nextSlide(): void
    {
        if ($this->viewerNavigationLocked()) {
            return;
        }

        $fragmentCount = $this->currentSlide()->fragments;

        if ($fragmentCount > 0 && $this->activeFragment < $fragmentCount - 1) {
            ++$this->activeFragment;

            return;
        }

        $this->activeFragment = -1;
        $this->activeIndex = min($this->activeIndex + 1, count($this->slides) - 1);
    }

    public function previousSlide(): void
    {
        if ($this->viewerNavigationLocked()) {
            return;
        }

        if ($this->activeFragment > -1) {
            --$this->activeFragment;

            return;
        }

        $previousIndex = max($this->activeIndex - 1, 0);

        if ($previousIndex === $this->activeIndex) {
            return;
        }

        $this->activeIndex = $previousIndex;
        $this->activeFragment = max($this->slides[$this->activeIndex]->fragments - 1, -1);
    }

    public function goToSlide(int $index, int $fragment = -1): void
    {
        if ($this->viewerNavigationLocked()) {
            return;
        }

        $this->activeIndex = $this->normalizeIndex($index);
        $this->activeFragment = $fragment;
    }

    public function navigateDown(): void
    {
        if ($this->viewerNavigationLocked()) {
            return;
        }

        $current = $this->currentSlide();
        $h = $current->h;
        $v = $current->v;
        $maxV = ($this->gridShape[$h] ?? 1) - 1;

        if ($v >= $maxV) {
            return;
        }

        $targetIndex = $this->findFlatIndex($h, $v + 1);

        if ($targetIndex !== null) {
            $this->activeFragment = -1;
            $this->activeIndex = $targetIndex;
        }
    }

    public function navigateUp(): void
    {
        if ($this->viewerNavigationLocked()) {
            return;
        }

        $current = $this->currentSlide();
        $h = $current->h;
        $v = $current->v;

        if ($v <= 0) {
            return;
        }

        $targetIndex = $this->findFlatIndex($h, $v - 1);

        if ($targetIndex !== null) {
            $this->activeFragment = -1;
            $this->activeIndex = $targetIndex;
        }
    }

    public function render(): View
    {
        $settingsResolver = app(EffectiveSettingsResolver::class);
        $themeResolver = app(ThemeResolver::class);
        $viewDataFactory = app(SlideViewDataFactory::class);
        $slidesConfig = config('slidewire.slides', new SlidesConfig());

        $effectiveSlides = $settingsResolver->resolve($this->slides, $this->deckMeta);
        $configuredThemes = $themeResolver->backgroundClassMap();
        $themeTypography = $themeResolver->typographyClassMap();
        $defaultTheme = (string) ($this->deckMeta['theme'] ?? $slidesConfig->theme);

        return view('slidewire::livewire.presentation-deck', [
            'slidesConfig' => $slidesConfig,
            'configuredThemes' => $configuredThemes,
            'defaultTheme' => $defaultTheme,
            'deckPayload' => $viewDataFactory->buildDeckPayload($effectiveSlides),
            'slideFrames' => $viewDataFactory->buildSlideFrames($effectiveSlides, $themeTypography, defaultTheme: $defaultTheme),
            'themeTypography' => $themeTypography,
            'googleFontsUrl' => $themeResolver->googleFontsUrl(),
            'codeFontFamily' => $themeResolver->codeFontFamily(),
            'slideThemes' => $themeResolver->slideThemes($effectiveSlides),
            'hasVerticalSlides' => $themeResolver->hasVerticalSlides($this->gridShape),
            'showControls' => $viewDataFactory->resolveDeckFlag($this->deckMeta, 'show_controls', $slidesConfig->showControls),
            'showProgress' => $viewDataFactory->resolveDeckFlag($this->deckMeta, 'show_progress', $slidesConfig->showProgress),
            'showFullscreenButton' => $viewDataFactory->resolveDeckFlag($this->deckMeta, 'show_fullscreen_button', $slidesConfig->showFullscreenButton),
        ]);
    }

    public function dehydrate(): void
    {
        if ($this->remoteMode !== 'controller' || $this->remoteSessionKey === null) {
            return;
        }

        app(RemoteSessionManager::class)->update(
            $this->remoteSessionKey,
            $this->activeIndex,
            $this->activeFragment,
            $this->viewerControls,
        );
    }

    public function toggleViewerControls(): void
    {
        if ($this->remoteMode !== 'controller') {
            return;
        }

        $this->viewerControls = ! $this->viewerControls;

        // Only a boolean the frontend handles via Alpine changes here; skip the
        // re-render so the DOM morph doesn't wipe client-applied fragment and
        // animation classes (which would flicker the slide's revealed items).
        $this->skipRender();
    }

    public function endRemoteSession(): void
    {
        if ($this->remoteMode !== 'controller' || $this->remoteSessionKey === null) {
            return;
        }

        app(RemoteSessionManager::class)->delete($this->remoteSessionKey);

        $this->remoteMode = 'solo';
        $this->remoteSessionKey = null;

        // The controller UI hides itself via Alpine (x-show on remoteMode); skip the
        // re-render so ending the session doesn't morph away revealed slide items.
        $this->skipRender();
    }

    public function pollRemoteState(): void
    {
        if ($this->remoteMode !== 'viewer' || $this->remoteSessionKey === null) {
            return;
        }

        $state = app(RemoteSessionManager::class)->get($this->remoteSessionKey);

        if ($state === null) {
            $this->remoteMode = 'solo';
            $this->remoteSessionKey = null;

            return;
        }

        $this->viewerControls = $state->viewerControls;

        if (
            $this->lastControllerIndex !== $state->index
            || $this->lastControllerFragment !== $state->fragment
        ) {
            $this->activeIndex = $this->normalizeIndex($state->index);
            $this->activeFragment = $state->fragment;
            $this->lastControllerIndex = $state->index;
            $this->lastControllerFragment = $state->fragment;

            return;
        }

        // No controller change — skip the render so a free-browsing viewer isn't
        // snapped back and diagrams/fragments don't flash on every poll tick.
        $this->skipRender();
    }

    protected function currentSlide(): Slide
    {
        return $this->slides[$this->activeIndex];
    }

    protected function normalizeIndex(int $index): int
    {
        return max(0, min($index, count($this->slides) - 1));
    }

    protected function findFlatIndex(int $h, int $v): ?int
    {
        return array_find_key($this->slides, fn (Slide $slide): bool => $slide->h === $h && $slide->v === $v);
    }

    /**
     * Validate the controller signature against the presentation's page route.
     *
     * Livewire update requests hit /livewire/update, not the signed page URL, so
     * we reconstruct the page request from the route + query params and validate
     * that — matching how the signed URL was minted by slidewire:remote.
     */
    protected function hasValidControllerSignature(): bool
    {
        $routeName = 'slidewire.' . str_replace('/', '.', $this->presentation);
        /** @var array<string, string> $queryParams */
        $queryParams = Request::query();

        return URL::hasValidSignature(HttpRequest::create(route($routeName, $queryParams)));
    }

    private function initializeRemoteSession(?int $startSlide): void
    {
        $remoteKey = Request::query('remote');

        if (! is_string($remoteKey) || $remoteKey === '') {
            return;
        }

        $manager = app(RemoteSessionManager::class);
        $state = $manager->get($remoteKey);

        if ($state === null || $state->presentation !== $this->presentation) {
            return;
        }

        $this->remoteSessionKey = $remoteKey;
        $this->pollInterval = $state->pollInterval;
        $this->viewerControls = $state->viewerControls;

        if ($this->hasValidControllerSignature()) {
            $this->remoteMode = 'controller';

            // Resume the live session position so the initial dehydrate re-writes
            // the current slide instead of resetting viewers back to the start.
            if ($startSlide === null) {
                $this->activeIndex = $this->normalizeIndex($state->index);
                $this->activeFragment = $state->fragment;
            }

            return;
        }

        $this->remoteMode = 'viewer';
        $this->lastControllerIndex = $state->index;
        $this->lastControllerFragment = $state->fragment;
        $this->activeIndex = $this->normalizeIndex($state->index);
        $this->activeFragment = $state->fragment;
    }

    private function viewerNavigationLocked(): bool
    {
        return $this->remoteMode === 'viewer' && ! $this->viewerControls;
    }
}

<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use WendellAdriel\SlideWire\DTOs\Slide;
use WendellAdriel\SlideWire\DTOs\SlidesConfig;
use WendellAdriel\SlideWire\Support\EffectiveSettingsResolver;
use WendellAdriel\SlideWire\Support\PresentationCompiler;
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
    }

    public function nextSlide(): void
    {
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
        $this->activeIndex = $this->normalizeIndex($index);
        $this->activeFragment = $fragment;
    }

    public function navigateDown(): void
    {
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
}

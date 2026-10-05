<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Support;

use WendellAdriel\SlideWire\DTOs\Slide;
use WendellAdriel\SlideWire\DTOs\SlidesConfig;

/**
 * Resolves effective runtime settings for each slide using the three-level precedence chain:
 *
 *   slide_meta > deck_meta > config slides
 *
 * @phpstan-type DeckMeta array<string, string>
 */
class EffectiveSettingsResolver
{
    /**
     * Runtime keys resolved through the precedence chain.
     *
     * @var list<string>
     */
    public const array RUNTIME_KEYS = [
        'theme', 'transition', 'transition_speed', 'transition_duration',
        'auto_slide', 'auto_slide_pause_on_interaction',
        'show_controls', 'show_progress', 'show_fullscreen_button',
        'keyboard', 'touch',
    ];

    /**
     * Resolve effective settings for a list of slides given deck-level metadata.
     *
     * @param  array<int, Slide>  $slides
     * @param  DeckMeta  $deckMeta
     * @return array<int, Slide>
     */
    public function resolve(array $slides, array $deckMeta): array
    {
        $slidesConfig = config('slidewire.slides', new SlidesConfig);

        return array_values(array_map(
            fn (Slide $slide): Slide => $this->resolveSlide($slide, $deckMeta, $slidesConfig),
            $slides,
        ));
    }

    /**
     * Resolve effective settings for a single slide.
     *
     * @param  DeckMeta  $deckMeta
     */
    protected function resolveSlide(Slide $slide, array $deckMeta, SlidesConfig $slidesConfig): Slide
    {
        $slideMeta = $slide->meta;
        $effective = [];

        foreach (self::RUNTIME_KEYS as $key) {
            $effective[$key] = $slideMeta[$key]
            ?? $deckMeta[$key]
            ?? $this->configValue($slidesConfig, $key);
        }

        $effective['highlight_theme'] = $slideMeta['highlight_theme']
            ?? $deckMeta['highlight_theme']
            ?? $slidesConfig->highlight->theme->value;

        return $slide->withEffective($effective);
    }

    protected function configValue(SlidesConfig $slidesConfig, string $key): ?string
    {
        return match ($key) {
            'theme' => $slidesConfig->theme,
            'transition' => $slidesConfig->transition->value,
            'transition_speed' => $slidesConfig->transitionSpeed->value,
            'transition_duration' => (string) $slidesConfig->transitionDuration,
            'auto_slide' => (string) $slidesConfig->autoSlide,
            'auto_slide_pause_on_interaction' => (string) $slidesConfig->autoSlidePauseOnInteraction,
            'show_controls' => (string) $slidesConfig->showControls,
            'show_progress' => (string) $slidesConfig->showProgress,
            'show_fullscreen_button' => (string) $slidesConfig->showFullscreenButton,
            'keyboard' => (string) $slidesConfig->keyboard,
            'touch' => (string) $slidesConfig->touch,
            default => null,
        };
    }
}

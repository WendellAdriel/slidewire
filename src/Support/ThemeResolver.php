<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Support;

use WendellAdriel\SlideWire\DTOs\FontConfig;
use WendellAdriel\SlideWire\DTOs\Slide;
use WendellAdriel\SlideWire\DTOs\SlidesConfig;
use WendellAdriel\SlideWire\DTOs\ThemeConfig;

class ThemeResolver
{
    /** @return array<string, string> */
    public function backgroundClassMap(): array
    {
        return collect(config('slidewire.themes', []))
            ->map(fn (ThemeConfig $theme): string => (string) $theme)
            ->all();
    }

    /** @return array<string, array{title: string, text: string}> */
    public function typographyClassMap(): array
    {
        return collect(config('slidewire.themes', []))
            ->map(fn (ThemeConfig $theme): array => [
                'title' => (string) $theme->title,
                'text' => (string) $theme->text,
            ])
            ->all();
    }

    public function codeFontFamily(): string
    {
        $slides = config('slidewire.slides', new SlidesConfig());
        $font = $slides->highlight->font;
        $fonts = config('slidewire.fonts', []);

        if ($font === '' || ! array_key_exists($font, $fonts)) {
            return $this->resolveFontStack();
        }

        return $this->resolveFontStack($font);
    }

    public function configuredFontFamily(?string $font, string $fallback = 'ui-sans-serif, system-ui, sans-serif'): ?string
    {
        $font = trim((string) $font);
        $fonts = config('slidewire.fonts', []);

        if ($font === '' || ! array_key_exists($font, $fonts)) {
            return null;
        }

        return "'{$font}', {$fallback}";
    }

    public function googleFontsUrl(): ?string
    {
        $fontConfig = config('slidewire.fonts', []);

        $googleFontFamilies = collect($fontConfig)
            ->filter(fn (FontConfig $config): bool => $config->isGoogle())
            ->map(function (FontConfig $font, string $family): string {
                $weights = $font->weights !== [] ? $font->weights : [400];
                $weightStr = implode(';', array_map(intval(...), $weights));

                $encodedFamily = urlencode($family);

                return "{$encodedFamily}:wght@{$weightStr}";
            })
            ->values()
            ->all();

        if ($googleFontFamilies === []) {
            return null;
        }

        $familyQuery = implode('&', array_map(fn (string $f): string => "family={$f}", $googleFontFamilies));

        return "https://fonts.googleapis.com/css2?{$familyQuery}&display=swap";
    }

    public function resolveFontStack(?string $font = null): string
    {
        $fallback = "ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace";
        $font = trim((string) $font);

        if ($font === '') {
            return $fallback;
        }

        return "'{$font}', {$fallback}";
    }

    /**
     * @param  array<int, Slide>  $effectiveSlides
     * @return array<int, string|null>
     */
    public function slideThemes(array $effectiveSlides): array
    {
        return array_values(array_map(
            fn (Slide $slide): ?string => $slide->effective['theme'] ?? null,
            $effectiveSlides,
        ));
    }

    /** @param  array<int, int>  $gridShape */
    public function hasVerticalSlides(array $gridShape): bool
    {
        return collect($gridShape)->contains(fn (int $count): bool => $count > 1);
    }
}

<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Support;

use Illuminate\Support\HtmlString;
use Phiki\Phiki;
use Phiki\Theme\Theme;
use Throwable;
use WendellAdriel\SlideWire\DTOs\SlidesConfig;
use WendellAdriel\SlideWire\DTOs\ThemeConfig;

class CodeHighlighter
{
    public function __construct(protected ThemeResolver $themeResolver) {}

    public function highlight(string $code, string $language, Theme|string|null $highlightTheme = null, ?string $presentationTheme = null, ?string $font = null, ?string $size = null): HtmlString
    {
        $slides = config('slidewire.slides', new SlidesConfig);

        if (! $slides->highlight->enabled) {
            return $this->fallback($code, $language, $font, $size);
        }

        $resolvedTheme = $this->resolveHighlightTheme($highlightTheme, $presentationTheme);

        $highlighted = $this->attemptPhikiHighlight($code, $language, $resolvedTheme, $font, $size);

        if ($highlighted !== null) {
            return new HtmlString($highlighted);
        }

        return $this->fallback($code, $language, $font, $size);
    }

    // Resolve highlight theme with explicit > theme > config precedence.
    public function resolveHighlightTheme(Theme|string|null $highlightTheme = null, ?string $presentationTheme = null): Theme
    {
        $explicitTheme = $this->normalizeTheme($highlightTheme);

        if ($explicitTheme instanceof Theme) {
            return $explicitTheme;
        }

        if ($presentationTheme !== null && $presentationTheme !== '') {
            $themeConfig = config('slidewire.themes', [])[$presentationTheme] ?? null;

            if ($themeConfig instanceof ThemeConfig) {
                return $themeConfig->highlightTheme;
            }
        }

        return config('slidewire.slides', new SlidesConfig)->highlight->theme;
    }

    // Replace fenced markdown code blocks with highlighted HTML.
    public function replaceCodeBlocks(string $markdown, Theme|string|null $highlightTheme = null, ?string $presentationTheme = null, ?string $font = null, ?string $size = null): string
    {
        return (string) preg_replace_callback('/```([\w-]*)\n(.*?)```/s', function (array $matches) use ($highlightTheme, $presentationTheme, $font, $size): string {
            $language = $matches[1] !== '' ? $matches[1] : 'text';
            $code = rtrim($matches[2]);

            return $this->highlight($code, $language, $highlightTheme, $presentationTheme, $font, $size)->toHtml();
        }, $markdown);
    }

    protected function attemptPhikiHighlight(string $code, string $language, Theme $theme, ?string $font = null, ?string $size = null): ?string
    {
        if (! class_exists(Phiki::class)) {
            return null;
        }

        try {
            $phiki = new Phiki;
            $result = $phiki->codeToHtml($code, $language, $theme)->toString();

            if (! is_string($result)) {
                return null;
            }

            return $this->applyCodeStyles($result, $font, $size);
        } catch (Throwable) {
            return null;
        }
    }

    protected function fallback(string $code, string $language, ?string $font = null, ?string $size = null): HtmlString
    {
        $style = $this->styleAttribute($font);
        $class = $this->classAttribute($size);
        $escapedLanguage = e($language);
        $escapedCode = e($code);

        return new HtmlString(
            "<pre class=\"slidewire-code{$class}\"{$style}><code class=\"language-{$escapedLanguage}\">{$escapedCode}</code></pre>"
        );
    }

    protected function applyCodeStyles(string $html, ?string $font = null, ?string $size = null): string
    {
        $style = $this->styleAttribute($font);
        $class = $this->classAttribute($size);

        if ($style === '' && $class === '') {
            return $html;
        }

        if ($class !== '') {
            $html = $this->mergeClassAttribute($html, 'pre', $class);
        }

        if ($style === '') {
            return $html;
        }

        $html = $this->mergeStyleAttribute($html, 'pre', $style);

        return $this->mergeStyleAttribute($html, 'code', $style);
    }

    protected function styleAttribute(?string $font = null): string
    {
        $highlight = config('slidewire.slides', new SlidesConfig)->highlight;
        $font = trim((string) ($font ?? $highlight->font));

        if ($font !== '') {
            $fontStack = $this->themeResolver->resolveFontStack($font);

            return ' style="font-family: ' . e($fontStack) . '"';
        }

        return '';
    }

    protected function classAttribute(?string $size = null): string
    {
        $highlight = config('slidewire.slides', new SlidesConfig)->highlight;
        $size = trim((string) ($size ?? $highlight->fontSize));

        return $size === '' ? '' : ' ' . e($size);
    }

    protected function mergeStyleAttribute(string $html, string $tag, string $style): string
    {
        $styleValue = trim((string) preg_replace('/^ style="(.*)"$/', '$1', $style));

        return $this->mergeAttribute($html, $tag, 'style', $styleValue, static function (string $existing, string $value): string {
            $existing = rtrim(trim($existing), ';');

            return $existing === '' ? $value : "{$existing}; {$value}";
        });
    }

    protected function mergeClassAttribute(string $html, string $tag, string $class): string
    {
        $classValue = trim($class);

        return $this->mergeAttribute($html, $tag, 'class', $classValue, static fn (string $existing, string $value): string => trim("{$existing} {$value}"));
    }

    protected function mergeAttribute(string $html, string $tag, string $attribute, string $value, callable $merge): string
    {
        if ($value === '') {
            return $html;
        }

        $updated = preg_replace_callback(
            "/<{$tag}([^>]*)>/i",
            static function (array $matches) use ($tag, $attribute, $value, $merge): string {
                $attributes = $matches[1];

                if (preg_match('/\s' . $attribute . '="([^"]*)"/i', $attributes, $attributeMatch) === 1) {
                    $mergedValue = $merge($attributeMatch[1], $value);
                    $attributes = preg_replace('/\s' . $attribute . '="[^"]*"/i', ' ' . $attribute . '="' . $mergedValue . '"', $attributes, 1);

                    return "<{$tag}{$attributes}>";
                }

                return "<{$tag}{$attributes} {$attribute}=\"{$value}\">";
            },
            $html,
            1,
        );

        return is_string($updated) ? $updated : $html;
    }

    protected function normalizeTheme(Theme|string|null $theme): ?Theme
    {
        if ($theme instanceof Theme) {
            return $theme;
        }

        if (! is_string($theme) || trim($theme) === '') {
            return null;
        }

        return Theme::tryFrom(trim($theme));
    }
}

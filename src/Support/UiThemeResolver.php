<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Support;

class UiThemeResolver
{
    public function __construct(protected SlideContext $context) {}

    public function theme(?string $theme = null): string
    {
        $themes = config('slidewire.themes', []);
        $candidate = trim($theme ?? $this->context->presentationTheme() ?? 'default');

        if ($candidate !== '' && array_key_exists($candidate, $themes)) {
            return $candidate;
        }

        if (array_key_exists('default', $themes)) {
            return 'default';
        }

        return (string) (array_key_first($themes) ?? 'default');
    }

    public function isLight(?string $theme = null): bool
    {
        return in_array($this->theme($theme), ['white', 'solarized'], true);
    }

    /** @return array{surface: string, muted_surface: string, glass_surface: string, border: string, heading_text: string, body_text: string, muted_text: string, accent: string, accent_soft: string, subtle_glow: string} */
    public function tokens(?string $theme = null): array
    {
        return match ($this->theme($theme)) {
            'white' => [
                'surface' => 'bg-white/88',
                'muted_surface' => 'bg-zinc-950/4',
                'glass_surface' => 'bg-white/72 backdrop-blur-xl',
                'border' => 'border-zinc-950/10',
                'heading_text' => 'text-zinc-950',
                'body_text' => 'text-zinc-700',
                'muted_text' => 'text-zinc-500',
                'accent' => 'text-cyan-700',
                'accent_soft' => 'bg-cyan-500/10',
                'subtle_glow' => 'shadow-[0_24px_80px_rgba(6,182,212,0.12)]',
            ],
            'solarized' => [
                'surface' => 'bg-yellow-50/88',
                'muted_surface' => 'bg-amber-950/5',
                'glass_surface' => 'bg-yellow-50/78 backdrop-blur-xl',
                'border' => 'border-amber-950/10',
                'heading_text' => 'text-amber-950',
                'body_text' => 'text-amber-900/85',
                'muted_text' => 'text-amber-900/60',
                'accent' => 'text-cyan-800',
                'accent_soft' => 'bg-cyan-600/10',
                'subtle_glow' => 'shadow-[0_24px_80px_rgba(14,116,144,0.12)]',
            ],
            'aurora' => [
                'surface' => 'bg-zinc-950/48',
                'muted_surface' => 'bg-white/6',
                'glass_surface' => 'bg-zinc-950/42 backdrop-blur-xl',
                'border' => 'border-white/12',
                'heading_text' => 'text-emerald-50',
                'body_text' => 'text-cyan-100/90',
                'muted_text' => 'text-cyan-100/68',
                'accent' => 'text-emerald-200',
                'accent_soft' => 'bg-emerald-400/12',
                'subtle_glow' => 'shadow-[0_24px_80px_rgba(16,185,129,0.18)]',
            ],
            'sunset' => [
                'surface' => 'bg-zinc-950/38',
                'muted_surface' => 'bg-white/7',
                'glass_surface' => 'bg-zinc-950/34 backdrop-blur-xl',
                'border' => 'border-white/12',
                'heading_text' => 'text-orange-50',
                'body_text' => 'text-amber-100/92',
                'muted_text' => 'text-orange-100/70',
                'accent' => 'text-amber-200',
                'accent_soft' => 'bg-amber-300/14',
                'subtle_glow' => 'shadow-[0_24px_80px_rgba(251,146,60,0.18)]',
            ],
            'neon' => [
                'surface' => 'bg-zinc-950/42',
                'muted_surface' => 'bg-white/7',
                'glass_surface' => 'bg-zinc-950/38 backdrop-blur-xl',
                'border' => 'border-white/12',
                'heading_text' => 'text-fuchsia-50',
                'body_text' => 'text-cyan-100/90',
                'muted_text' => 'text-cyan-100/66',
                'accent' => 'text-fuchsia-200',
                'accent_soft' => 'bg-fuchsia-400/12',
                'subtle_glow' => 'shadow-[0_24px_80px_rgba(217,70,239,0.2)]',
            ],
            default => [
                'surface' => 'bg-zinc-950/44',
                'muted_surface' => 'bg-white/6',
                'glass_surface' => 'bg-zinc-950/40 backdrop-blur-xl',
                'border' => 'border-white/12',
                'heading_text' => 'text-white',
                'body_text' => 'text-zinc-100/88',
                'muted_text' => 'text-zinc-200/66',
                'accent' => 'text-cyan-200',
                'accent_soft' => 'bg-cyan-400/12',
                'subtle_glow' => 'shadow-[0_24px_80px_rgba(34,211,238,0.16)]',
            ],
        };
    }

    /** @return array{ring: string, text: string, soft: string} */
    public function tone(?string $tone = null, ?string $theme = null): array
    {
        $resolvedTone = trim((string) $tone);
        $isLight = $this->isLight($theme);

        return match ($resolvedTone) {
            'primary' => $isLight
                ? ['ring' => 'border-cyan-600/24', 'text' => 'text-cyan-700', 'soft' => 'bg-cyan-500/10']
                : ['ring' => 'border-cyan-300/24', 'text' => 'text-cyan-200', 'soft' => 'bg-cyan-300/10'],
            'success' => $isLight
                ? ['ring' => 'border-emerald-600/24', 'text' => 'text-emerald-700', 'soft' => 'bg-emerald-500/10']
                : ['ring' => 'border-emerald-300/24', 'text' => 'text-emerald-200', 'soft' => 'bg-emerald-300/10'],
            'warning' => $isLight
                ? ['ring' => 'border-amber-600/24', 'text' => 'text-amber-700', 'soft' => 'bg-amber-500/10']
                : ['ring' => 'border-amber-300/24', 'text' => 'text-amber-200', 'soft' => 'bg-amber-300/10'],
            'danger' => $isLight
                ? ['ring' => 'border-rose-600/24', 'text' => 'text-rose-700', 'soft' => 'bg-rose-500/10']
                : ['ring' => 'border-rose-300/24', 'text' => 'text-rose-200', 'soft' => 'bg-rose-300/10'],
            default => [
                'ring' => $this->tokens($theme)['border'],
                'text' => $this->tokens($theme)['accent'],
                'soft' => $this->tokens($theme)['accent_soft'],
            ],
        };
    }

    /** @return array{wrapper: string, header: string, body: string, footer: string, accent_chip: string} */
    public function panel(string $variant = 'default', ?string $tone = null, ?string $theme = null): array
    {
        $tokens = $this->tokens($theme);
        $palette = $this->tone($tone, $theme);

        $base = implode(' ', [
            'relative overflow-hidden rounded-[2rem] border',
            $tokens['border'],
            $tokens['subtle_glow'],
        ]);

        $variantClasses = match ($variant) {
            'glass' => implode(' ', [
                $tokens['glass_surface'],
                $palette['ring'],
                'before:pointer-events-none before:absolute before:inset-0 before:bg-[linear-gradient(135deg,rgba(255,255,255,0.18),transparent_24%,transparent_72%,rgba(255,255,255,0.08))] before:opacity-70',
                'after:pointer-events-none after:absolute after:inset-x-6 after:top-0 after:h-px after:bg-white/25 after:blur-sm',
            ]),
            'outlined' => implode(' ', [
                'bg-transparent border-dashed',
                $palette['ring'],
                'shadow-none',
            ]),
            'elevated' => implode(' ', [
                $this->isLight($theme) ? 'bg-white/96' : 'bg-zinc-950/82',
                $palette['ring'],
                'backdrop-blur-sm shadow-[0_18px_30px_rgba(15,23,42,0.22),0_42px_100px_rgba(15,23,42,0.34)] ring-1 ring-inset ring-white/10 before:pointer-events-none before:absolute before:inset-x-5 before:top-0 before:h-10 before:rounded-t-[1.75rem] before:bg-[linear-gradient(180deg,rgba(255,255,255,0.14),transparent)] before:opacity-90',
            ]),
            default => implode(' ', [
                $tokens['muted_surface'],
                $palette['ring'],
                'shadow-[inset_0_1px_0_rgba(255,255,255,0.04)] border-white/8',
            ]),
        };

        return [
            'wrapper' => trim($base . ' ' . $variantClasses),
            'header' => trim('relative flex flex-col gap-3 border-b px-6 py-5 sm:px-7 ' . $tokens['border']),
            'body' => 'relative px-6 py-5 sm:px-7 sm:py-6',
            'footer' => trim('relative border-t px-6 py-4 sm:px-7 ' . $tokens['border']),
            'accent_chip' => trim('inline-flex items-center rounded-full px-3 py-1 text-[0.8125rem] font-medium ' . $palette['soft'] . ' ' . $palette['text']),
        ];
    }

    public function heading(?string $theme = null): string
    {
        $tokens = $this->tokens($theme);

        return trim('max-w-[24ch] text-balance text-4xl font-semibold tracking-tight sm:text-3xl ' . $tokens['heading_text']);
    }

    public function body(?string $theme = null): string
    {
        $tokens = $this->tokens($theme);

        return trim('max-w-[48ch] text-pretty text-lg/7 sm:text-base/7 ' . $tokens['body_text']);
    }

    public function meta(?string $theme = null): string
    {
        $tokens = $this->tokens($theme);

        return trim('text-sm/6 sm:text-[0.8125rem]/6 ' . $tokens['muted_text']);
    }

    public function overline(?string $theme = null, bool $mono = false): string
    {
        $tokens = $this->tokens($theme);
        $tracking = $mono ? 'uppercase tracking-wide' : 'tracking-[0.24em]';

        return trim('text-sm/6 sm:text-[0.8125rem]/6 font-medium ' . $tracking . ' ' . $tokens['accent']);
    }

    public function gap(string $gap = 'lg'): string
    {
        return match ($gap) {
            'md' => 'gap-5 sm:gap-4',
            'xl' => 'gap-9 sm:gap-8',
            default => 'gap-7 sm:gap-6',
        };
    }

    /** @param  array<string, string>  $map */
    public function select(string $value, array $map, string $default): string
    {
        return $map[$value] ?? $map[$default];
    }

    public function configuredFontFamily(?string $font, string $fallback = 'ui-sans-serif, system-ui, sans-serif'): ?string
    {
        $font = trim((string) $font);
        $fonts = config('slidewire.fonts', []);

        if ($font === '' || ! array_key_exists($font, $fonts)) {
            return null;
        }

        return "font-family: '{$font}', {$fallback};";
    }
}

<?php

declare(strict_types=1);

use Phiki\Theme\Theme;
use WendellAdriel\SlideWire\DTOs\FontConfig;
use WendellAdriel\SlideWire\DTOs\HighlightConfig;
use WendellAdriel\SlideWire\DTOs\SlidesConfig;
use WendellAdriel\SlideWire\DTOs\ThemeConfig;
use WendellAdriel\SlideWire\DTOs\ThemeFont;
use WendellAdriel\SlideWire\Enums\FontSource;
use WendellAdriel\SlideWire\Enums\SlideTransition;
use WendellAdriel\SlideWire\Enums\SlideTransitionSpeed;
use WendellAdriel\SlideWire\Support\ConfigValidator;

it('validates the default config without errors', function (): void {
    $validator = new ConfigValidator();
    $validator->validate();
})->throwsNoExceptions();

it('rejects non-dto theme entries', function (): void {
    $validator = new ConfigValidator();
    $validator->validateThemes(['broken' => 'just-a-string']);
})->throws(InvalidArgumentException::class, 'must be a ThemeConfig');

it('rejects themes with empty background', function (): void {
    $validator = new ConfigValidator();
    $validator->validateThemes([
        'broken' => new ThemeConfig(
            background: '',
            highlightTheme: Theme::GithubDark,
            title: new ThemeFont('Inter', 'text-white', 'text-4xl'),
            text: new ThemeFont('Inter', 'text-slate-300', 'text-lg'),
        ),
    ]);
})->throws(InvalidArgumentException::class, 'missing required key [background]');

it('rejects themes with empty title typography values', function (): void {
    $validator = new ConfigValidator();
    $validator->validateThemes([
        'broken' => new ThemeConfig(
            background: 'bg-red',
            highlightTheme: Theme::GithubDark,
            title: new ThemeFont('', 'text-white', 'text-4xl'),
            text: new ThemeFont('Inter', 'text-slate-300', 'text-lg'),
        ),
    ]);
})->throws(InvalidArgumentException::class, 'missing required key [font]');

it('rejects themes with empty typography size', function (): void {
    $validator = new ConfigValidator();
    $validator->validateThemes([
        'broken' => new ThemeConfig(
            background: 'bg-red',
            highlightTheme: Theme::GithubDark,
            title: new ThemeFont('Inter', 'text-white', ''),
            text: new ThemeFont('Inter', 'text-slate-300', 'text-lg'),
        ),
    ]);
})->throws(InvalidArgumentException::class, 'missing required key [size]');

it('rejects non-dto font entries', function (): void {
    $validator = new ConfigValidator();
    $validator->validateFonts(['BadFont' => 'google']);
})->throws(InvalidArgumentException::class, 'must be a FontConfig');

it('accepts valid font configurations', function (): void {
    $validator = new ConfigValidator();
    $validator->validateFonts([
        'Inter' => new FontConfig(FontSource::Google, [400, 700]),
        'Georgia' => new FontConfig(FontSource::System),
    ]);
})->throwsNoExceptions();

it('accepts valid slide settings', function (): void {
    $validator = new ConfigValidator();
    $validator->validateSlides(new SlidesConfig(
        transition: SlideTransition::Fade,
        transitionSpeed: SlideTransitionSpeed::Fast,
        highlight: new HighlightConfig(fontSize: 'text-lg'),
    ));
})->throwsNoExceptions();

it('validates font weights must be integers', function (): void {
    $validator = new ConfigValidator();
    $validator->validateFonts(['Inter' => new FontConfig(FontSource::Google, ['400'])]);
})->throws(InvalidArgumentException::class, 'weights must be an array of integers');

it('rejects empty highlight font size', function (): void {
    $validator = new ConfigValidator();
    $validator->validateSlides(new SlidesConfig(
        highlight: new HighlightConfig(fontSize: '  '),
    ));
})->throws(InvalidArgumentException::class, 'font_size must be a non-empty string');

it('supports var export hydration for slidewire config dto objects', function (): void {
    $slides = new SlidesConfig(
        transition: SlideTransition::Fade,
        transitionSpeed: SlideTransitionSpeed::Fast,
        highlight: new HighlightConfig(
            enabled: false,
            theme: Theme::GithubDark,
            font: 'FiraCode',
            fontSize: 'text-sm',
        ),
    );

    $theme = new ThemeConfig(
        background: 'bg-slate-950 text-white',
        highlightTheme: Theme::GithubDark,
        title: new ThemeFont('Sora', 'text-white', 'text-5xl'),
        text: new ThemeFont('Sora', 'text-slate-200', 'text-lg'),
    );

    $font = new FontConfig(FontSource::Google, [400, 600, 700]);

    /** @var SlidesConfig $rehydratedSlides */
    $rehydratedSlides = eval('return ' . var_export($slides, true) . ';');
    /** @var ThemeConfig $rehydratedTheme */
    $rehydratedTheme = eval('return ' . var_export($theme, true) . ';');
    /** @var FontConfig $rehydratedFont */
    $rehydratedFont = eval('return ' . var_export($font, true) . ';');

    expect($rehydratedSlides)
        ->toEqual($slides)
        ->and($rehydratedTheme)->toEqual($theme)
        ->and($rehydratedFont)->toEqual($font);
});

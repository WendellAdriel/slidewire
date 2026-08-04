<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Support;

use InvalidArgumentException;
use Phiki\Theme\Theme;
use WendellAdriel\SlideWire\DTOs\FontConfig;
use WendellAdriel\SlideWire\DTOs\RemoteConfig;
use WendellAdriel\SlideWire\DTOs\SlidesConfig;
use WendellAdriel\SlideWire\DTOs\ThemeConfig;
use WendellAdriel\SlideWire\DTOs\ThemeFont;
use WendellAdriel\SlideWire\Enums\FontSource;
use WendellAdriel\SlideWire\Enums\SlideTransition;
use WendellAdriel\SlideWire\Enums\SlideTransitionSpeed;

// Validates SlideWire config before runtime use.
class ConfigValidator
{
    /**
     * @var list<string>
     */
    private const array TYPOGRAPHY_REQUIRED_KEYS = ['font', 'color', 'size'];

    /**
     * @param  array<string, ThemeConfig>  $themes
     *
     * @throws InvalidArgumentException when a theme entry is malformed
     */
    public function validateThemes(array $themes): void
    {
        foreach ($themes as $name => $theme) {
            if (! ($theme instanceof ThemeConfig)) {
                throw new InvalidArgumentException(
                    "SlideWire theme [{$name}] must be a ThemeConfig"
                );
            }

            if ($theme->background === '') {
                throw new InvalidArgumentException(
                    "SlideWire theme [{$name}] is missing required key [background]."
                );
            }

            if (! ($theme->highlightTheme instanceof Theme)) {
                throw new InvalidArgumentException(
                    "SlideWire theme [{$name}] is missing required key [highlight_theme]."
                );
            }

            $this->validateThemeTypography($name, $theme->title, 'title');
            $this->validateThemeTypography($name, $theme->text, 'text');
        }
    }

    /**
     * @param  array<string, FontConfig>  $fonts
     *
     * @throws InvalidArgumentException when a font entry is malformed
     */
    public function validateFonts(array $fonts): void
    {
        foreach ($fonts as $family => $config) {
            if (! ($config instanceof FontConfig)) {
                throw new InvalidArgumentException(
                    "SlideWire font [{$family}] must be a FontConfig with at least a 'source' key."
                );
            }

            $this->validateFontConfig($family, $config);
        }
    }

    /** @throws InvalidArgumentException when a slide config value is invalid */
    public function validateSlides(SlidesConfig $slides): void
    {
        if (! ($slides->transition instanceof SlideTransition)) {
            throw new InvalidArgumentException(
                'SlideWire slide transition is invalid. Valid transitions: ' . implode(', ', SlideTransition::values()) . '.'
            );
        }

        if (! ($slides->transitionSpeed instanceof SlideTransitionSpeed)) {
            throw new InvalidArgumentException(
                'SlideWire slide transition_speed is invalid. Valid speeds: ' . implode(', ', SlideTransitionSpeed::values()) . '.'
            );
        }

        if (! ($slides->highlight->theme instanceof Theme)) {
            throw new InvalidArgumentException(
                'SlideWire slides highlight theme must be a valid Phiki theme enum.'
            );
        }

        if (trim($slides->highlight->fontSize) === '') {
            throw new InvalidArgumentException(
                'SlideWire slides highlight font_size must be a non-empty string.'
            );
        }
    }

    /** @throws InvalidArgumentException when a remote config value is invalid */
    public function validateRemote(RemoteConfig $remote): void
    {
        if (preg_match(RemoteConfig::TTL_PATTERN, $remote->ttl) !== 1) {
            throw new InvalidArgumentException(
                "SlideWire remote ttl [{$remote->ttl}] is invalid. Use formats like '30m', '2h', or '1d'."
            );
        }

        if (preg_match(RemoteConfig::POLL_PATTERN, $remote->pollInterval) !== 1) {
            throw new InvalidArgumentException(
                "SlideWire remote poll_interval [{$remote->pollInterval}] is invalid. Use formats like '750ms' or '1s'."
            );
        }
    }

    /** @throws InvalidArgumentException on invalid configuration */
    public function validate(): void
    {
        $this->validateThemes(config('slidewire.themes', []));
        $this->validateFonts(config('slidewire.fonts', []));
        $this->validateSlides(config('slidewire.slides', new SlidesConfig()));

        $remote = config('slidewire.remote', new RemoteConfig());

        if (! ($remote instanceof RemoteConfig)) {
            throw new InvalidArgumentException('SlideWire remote config must be a RemoteConfig instance.');
        }

        $this->validateRemote($remote);
    }

    protected function validateThemeTypography(string $themeName, ThemeFont $font, string $key): void
    {
        foreach (self::TYPOGRAPHY_REQUIRED_KEYS as $requiredKey) {
            if ($font->{$requiredKey} === '') {
                throw new InvalidArgumentException(
                    "SlideWire theme [{$themeName}] typography [{$key}] is missing required key [{$requiredKey}]."
                );
            }
        }
    }

    protected function validateFontConfig(string $family, FontConfig $config): void
    {
        if ((string) $config === '') {
            throw new InvalidArgumentException(
                "SlideWire font [{$family}] is missing required key [source]."
            );
        }

        $validSources = array_map(static fn (FontSource $source): string => $source->value, FontSource::cases());

        if (! in_array($config->source->value, $validSources, true)) {
            throw new InvalidArgumentException(
                "SlideWire font [{$family}] has invalid source [{$config->source->value}]. Valid sources: " . implode(', ', $validSources) . '.'
            );
        }

        foreach ($config->weights as $weight) {
            if (! is_int($weight)) {
                throw new InvalidArgumentException(
                    "SlideWire font [{$family}] weights must be an array of integers."
                );
            }
        }
    }
}

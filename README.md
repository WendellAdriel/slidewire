<div align="center">
    <img src="https://github.com/WendellAdriel/slidewire/raw/main/art/logo.png" alt="SlideWire logo" height="220"/>
    <p>
        <h1>SlideWire</h1>
        Create beautiful presentations powered by Livewire
    </p>
</div>

<p align="center">
    <a href="https://packagist.org/packages/wendelladriel/slidewire"><img src="https://img.shields.io/packagist/v/wendelladriel/slidewire.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/wendelladriel/slidewire"><img src="https://img.shields.io/packagist/php-v/wendelladriel/slidewire.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/wendelladriel/slidewire"><img src="https://badge.laravel.cloud/badge/wendelladriel/slidewire?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/WendellAdriel/slidewire/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/WendellAdriel/slidewire/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://slidewire.dev"><img src="https://img.shields.io/badge/docs-website-blue?logo=readthedocs&style=flat-square" alt="Documentation"></a>
    <a href="https://packagist.org/packages/wendelladriel/slidewire"><img src="https://img.shields.io/packagist/dt/wendelladriel/slidewire.svg?style=flat-square" alt="Total Downloads"></a>
</p>

## Installation

You can install the package via composer:

```bash
composer require wendelladriel/slidewire
```

## Usage

SlideWire is a Laravel package for building presentation decks with Livewire. Presentations are built as Blade files, rendered as a full-page Livewire experience, and support navigation, themes, fragments, code highlighting, diagrams, vertical stacks, and timed auto-slide flows.

### Features

- Full-page deck rendering with Livewire
- Keyboard, click, swipe, and hash-based navigation
- Horizontal slides with nested vertical slide groups
- Directional controls, progress, and fullscreen support
- Transition presets, fragments, and auto-animate
- Auto-slide timers with config, deck, and slide precedence
- Syntax highlighting with Phiki and theme-aware configuration
- Reveal-style backgrounds with color, image, and video support
- Structured theme presets with typography controls
- Remote presenter control — share a link and every viewer's deck follows the presenter live (polling-based, no extra infrastructure)

### Remote Presenter Control

Drive every connected viewer's deck from a single presenter link — no websocket
server required. Start a session for a registered presentation:

```bash
php artisan slidewire:remote pitch --ttl=2h --poll=2s
```

This prints two URLs:

- **Controller** — a signed link for the presenter. Navigating it publishes the
  current slide/fragment to the cache.
- **Viewer** — a shareable link for the audience. Each viewer polls the session
  and follows the presenter's position.

Modes are selected by query parameters on the existing SlideWire route, so no new
routes are added and a deck opened without a `remote` parameter behaves exactly as
a normal solo presentation. Defaults live in the `RemoteConfig` DTO under the
`remote` key in `config/slidewire.php` (`ttl`, `pollInterval`, `viewerControls`,
`cacheStore`); `--ttl` and `--poll` override the TTL and viewer poll cadence per
session. Viewers are locked to the presenter by default — the controller can
unlock free browsing (and end the session) from an in-deck control.

Access the full documentation [here](https://slidewire.dev).

## Changelog

Please see the [changelog](https://slidewire.dev/docs/changelog) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to SlideWire! You can read the contribution guide [here](.github/CONTRIBUTING.md).

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Wendell Adriel](https://github.com/WendellAdriel)
- [All Contributors](../../contributors)

## License

SlideWire is open-sourced software licensed under the [MIT license](LICENSE).

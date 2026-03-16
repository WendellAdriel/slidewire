---
name: slidewire-development
description: Guidance for generating, authoring, and refining beautiful SlideWire presentations in Laravel applications.
metadata:
---

# SlideWire Development

Use this skill when working with `wendelladriel/slidewire`: creating new presentations, improving existing decks, adding navigation-friendly structure, or styling slides with themes, text, images, markdown, code, diagrams, and fragments.

## Start with the SlideWire workflow

Default workflow for a new presentation:

1. Generate the scaffold with `make:slidewire`.
2. Author the deck in a single Blade presentation file.
3. Register the route with `Route::slidewire()`.
4. Refine the deck with themes, transitions, fragments, and supporting components.

Use the package command instead of hand-writing the initial file when possible:

```bash
php artisan make:slidewire demo/product-launch --title="Product Launch"
```

- Presentations are discovered from `config('slidewire.presentation_roots')`.
- By default, files live under `resources/views/pages/slides`.
- A presentation key like `demo/product-launch` maps to `resources/views/pages/slides/demo/product-launch.blade.php`.

## Preferred deck structure

Each presentation should be a single Blade file with one `<x-slidewire::deck>` containing one or more `<x-slidewire::slide>` components.

```blade
<x-slidewire::deck>
    <x-slidewire::slide class="bg-slate-900 text-white">
        <h1 class="text-4xl font-semibold tracking-tight">Product Launch</h1>
        <p class="text-lg text-slate-300">Opening slide</p>
    </x-slidewire::slide>

    <x-slidewire::slide class="bg-white text-slate-900">
        <x-slidewire::markdown>
## Metrics

- Activation: 62%
- Churn: 1.8%
        </x-slidewire::markdown>
    </x-slidewire::slide>
</x-slidewire::deck>
```

Strong defaults:

- Keep one presentation per file.
- Use deck-level defaults for repeated settings.
- Use slide-level overrides only when a slide should intentionally differ.
- Use Tailwind classes for layout, spacing, colors, and typography.

## Use the right SlideWire components

### Core components

- `<x-slidewire::deck>`: presentation wrapper for deck-wide defaults.
- `<x-slidewire::slide>`: a single slide, with support for metadata like `theme`, `transition`, `transition-speed`, `auto-slide`, `auto-animate`, and background attributes.
- `<x-slidewire::vertical-slide>`: groups slides into a vertical stack inside one horizontal column.
- `<x-slidewire::fragment>`: reveals content progressively.
- `<x-slidewire::text>`: semantic text wrapper with optional orientation and component-level animation hooks.
- `<x-slidewire::image>`: native image wrapper with component-level animation hooks.
- `<x-slidewire::markdown>`: renders markdown and highlighted code fences.
- `<x-slidewire::code>`: renders highlighted code blocks directly.
- `<x-slidewire::diagram>`: renders Mermaid diagrams.

### When to use each content component

- Use `text` for semantic headings, paragraphs, inline text, vertical labels, or reusable animation-ready copy blocks.
- Use `image` for native `<img>` output with SlideWire animation metadata.
- Use `markdown` for narrative slides, bullets, and mixed prose/code.
- Use `code` for tightly controlled code examples or language-specific snippets.
- Use `diagram` for flows, architecture, and process explanations.
- Use `fragment` for sequential reveals instead of overcrowding one slide.

### Text component guidance

Use `text` when you want semantic text output without hand-writing repeated animation and orientation attributes.

Supported attributes:

- `type`: `paragraph` (default), `inline`, `heading`
- `orientation`: `horizontal` (default), `vertical`
- `animation`
- `animation-speed`
- `class` and any other valid HTML attributes for the rendered tag

Examples:

```blade
<x-slidewire::text type="heading" class="text-5xl font-semibold tracking-tight">
    Product Launch
</x-slidewire::text>
```

```blade
<x-slidewire::text
    type="heading"
    orientation="vertical"
    animation="slide-up"
    animation-speed="slow"
    class="text-4xl"
>
    Launch Day
</x-slidewire::text>
```

Recommendations:

- Prefer `heading` for prominent slide titles when `h2` semantics make sense.
- Prefer `inline` for short labels embedded in richer layouts.
- Use `orientation="vertical"` for side labels or editorial layouts, not long paragraphs.
- Fall back to raw HTML when you need fully custom markup.

### Image component guidance

Use `image` when you want a normal `<img>` element with the same animation contract as other SlideWire content components.

Supported attributes:

- all standard image attributes like `src`, `alt`, `class`, `width`, `height`, `loading`, `decoding`, and `fetchpriority`
- `animation`
- `animation-speed`

Example:

```blade
<x-slidewire::image
    src="/images/product-shot.png"
    alt="Product shot"
    class="w-72 rounded-2xl shadow-2xl"
    loading="lazy"
    animation="pop"
    animation-speed="default"
/>
```

Recommendations:

- Always provide meaningful `alt` text unless the image is purely decorative.
- Keep sizing intentional with Tailwind classes or width/height attributes.
- Use native image attributes directly instead of expecting PHP-side prop mapping.

## Structure slides for presentation flow

### Horizontal and vertical navigation

Use regular `<x-slidewire::slide>` elements for left/right progression.

Use `<x-slidewire::vertical-slide>` when one topic needs a vertical drill-down:

```blade
<x-slidewire::deck>
    <x-slidewire::slide>
        <h2>Overview</h2>
    </x-slidewire::slide>

    <x-slidewire::vertical-slide>
        <x-slidewire::slide>
            <h2>Detail: Top</h2>
        </x-slidewire::slide>
        <x-slidewire::slide>
            <h2>Detail: Bottom</h2>
        </x-slidewire::slide>
    </x-slidewire::vertical-slide>
</x-slidewire::deck>
```

Behavior to preserve when editing decks:

- Left/right moves between horizontal columns.
- Up/down moves within a vertical stack.
- Space advances linearly through the presentation.
- Hash deep links use `#/slide/N` or `#/slide/H/V`.

## Prefer deck defaults, then override intentionally

SlideWire resolves runtime settings in this order:

```text
slide attribute -> deck attribute -> config('slidewire.slides')
```

Use deck-level attributes for shared presentation behavior:

```blade
<x-slidewire::deck theme="black" transition="fade" auto-slide="3000">
    <x-slidewire::slide>
        <h2>Inherits deck defaults</h2>
    </x-slidewire::slide>

    <x-slidewire::slide theme="white" transition="zoom">
        <h2>Overrides intentionally</h2>
    </x-slidewire::slide>
</x-slidewire::deck>
```

Common deck-level controls:

- `theme`
- `transition`
- `transition-speed`
- `transition-duration`
- `auto-slide`
- `auto-slide-pause-on-interaction`
- `show-controls`
- `show-progress`
- `show-fullscreen-button`
- `keyboard`
- `touch`
- `highlight-theme`

## Make decks visually strong

SlideWire is designed to work well with Tailwind and theme presets.

### Theme guidance

Built-in themes:

- `default`
- `black`
- `white`
- `aurora`
- `sunset`
- `neon`
- `solarized`

Use a theme when you want presentation-wide visual consistency. Define custom themes in `config/slidewire.php` with `ThemeConfig` and `ThemeFont` when the deck needs a distinct branded look.

### Background guidance

- Use Tailwind classes for solid and gradient backgrounds.
- Use slide metadata for image or video backgrounds.
- Keep foreground text contrast high against the chosen background.

Examples:

```blade
<x-slidewire::slide class="bg-gradient-to-br from-blue-900 to-slate-950 text-slate-50">
    <h2>Gradient slide</h2>
</x-slidewire::slide>
```

```blade
<x-slidewire::slide
    background-image="https://example.com/bg.jpg"
    background-size="cover"
    background-position="center"
    background-opacity="0.35"
>
    <h2>Image-backed slide</h2>
</x-slidewire::slide>
```

### Typography and font guidance

- Theme typography comes from the active `ThemeConfig`.
- Code highlighting uses `slides.highlight.font` and `slides.highlight.font_size` by default.
- Google Fonts configured in `config('slidewire.fonts')` are loaded automatically.
- Override code sizing per component with Tailwind classes like `text-sm`, `text-base`, `text-lg`, or `text-xl`.

## Use motion deliberately

Supported transition names:

- `slide`
- `fade`
- `zoom`
- `convex`
- `concave`
- `none`

Supported transition speeds:

- `fast`
- `default`
- `slow`

Recommendations:

- Default to one primary transition across the deck.
- Use `fade` or `zoom` only when the content change benefits from it.
- Use `auto-animate` for before/after or transformation sequences with matching element IDs.
- Use `auto-slide` sparingly for timed demos or kiosk-style decks.

### Component-level animations

`text` and `image` support element-level entry animations through `animation` and `animation-speed`.

Supported names:

- `fade`
- `pop`
- `zoom-in`
- `zoom-out`
- `slide-left`
- `slide-right`
- `slide-up`
- `slide-down`
- `blur`
- `typewriter` (`text` only)

Recommendations:

- Use element animations to emphasize key content inside a slide, not every element on the slide.
- Keep animation choices consistent within a deck.
- Reserve `typewriter` for short plain-text copy, not complex nested markup.
- Avoid relying on component animations as the only way content becomes understandable.
- Remember reduced-motion users may see the final state without the animation effect.

## Build content for presenters, not just readers

### Fragments

Use fragments to reveal talking points one at a time:

```blade
<x-slidewire::slide>
    <h2>Rollout plan</h2>
    <x-slidewire::fragment :index="0"><p>Private beta</p></x-slidewire::fragment>
    <x-slidewire::fragment :index="1"><p>Pilot accounts</p></x-slidewire::fragment>
    <x-slidewire::fragment :index="2"><p>General availability</p></x-slidewire::fragment>
</x-slidewire::slide>
```

### Code examples

Use the `code` component for exact control:

```blade
<x-slidewire::code language="php" size="text-lg">
echo 'highlighted example';
</x-slidewire::code>
```

Use `theme` or `font` overrides only when the default theme-coupled highlighting is not a good fit.

### Markdown

Use markdown for concise authoring and embedded code fences:

~~~blade
<x-slidewire::markdown>
## Launch Metrics

- Activation: 62%
- Churn: 1.8%
~~~
</x-slidewire::markdown>
~~~

### Diagrams

Use Mermaid syntax inside `diagram` for visual explanations:

```blade
<x-slidewire::diagram>
flowchart LR
    A[Start] --> B[Process]
    B --> C[End]
</x-slidewire::diagram>
```

## Route registration

Prefer the SlideWire route macro:

```php
use Illuminate\Support\Facades\Route;

Route::slidewire('/slides/product-launch', 'demo/product-launch');
```

This registers the Livewire presentation route, passes the presentation key through route defaults, and creates route names like `slidewire.demo.product-launch`.

## Refactoring checklist

When creating or updating a SlideWire presentation, verify that:

- the presentation file lives in a configured presentation root
- the deck has exactly one `<x-slidewire::deck>` wrapper
- horizontal and vertical slide grouping matches the intended navigation flow
- deck defaults are used for shared behavior instead of repeated slide attributes
- text contrast, spacing, and layout are clear on both dark and light backgrounds
- fragments improve pacing instead of hiding essential context
- text and image components are used where their semantics or animation hooks improve authoring clarity
- code, markdown, and diagram slides use the most appropriate component
- route registration points to the correct presentation key

## Good defaults to follow

- Start with `php artisan make:slidewire`.
- Keep presentations in one Blade file per deck.
- Use deck-level theme and transition defaults first.
- Use Tailwind classes for slide composition and atmosphere.
- Use `text` and `image` when you want semantic wrappers with built-in animation metadata.
- Use vertical slides for drill-downs, not for unrelated content.
- Use fragments to pace the narrative.
- Use markdown for fast authoring, `code` for exact snippets, and `diagram` for visual structure.

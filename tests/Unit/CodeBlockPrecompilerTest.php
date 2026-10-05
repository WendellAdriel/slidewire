<?php

declare(strict_types=1);

use WendellAdriel\SlideWire\Support\CodeBlockPrecompiler;

it('encodes fenced code blocks inside markdown component tags', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<x-slidewire::markdown>
```php
echo "hello";
```
</x-slidewire::markdown>';

    $result = $precompiler($template);

    expect($result)->not->toContain('```php')
        ->and($result)->toContain(CodeBlockPrecompiler::PLACEHOLDER_PREFIX)
        ->and($result)->toContain(CodeBlockPrecompiler::PLACEHOLDER_SUFFIX);
});

it('decodes encoded code blocks back to original fenced format', function (): void {
    $original = "```php\necho 'hello';\n```";
    $encoded = CodeBlockPrecompiler::PLACEHOLDER_PREFIX . base64_encode($original) . CodeBlockPrecompiler::PLACEHOLDER_SUFFIX;

    $decoded = CodeBlockPrecompiler::decode($encoded);

    expect($decoded)->toBe($original);
});

it('round-trips encode and decode correctly', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<x-slidewire::markdown>
## Title

```php
echo "hello";
```

Some text after.
</x-slidewire::markdown>';

    $encoded = $precompiler($template);
    $decoded = CodeBlockPrecompiler::decode($encoded);

    expect($decoded)->toContain('```php')
        ->and($decoded)->toContain('echo "hello";')
        ->and($decoded)->toContain('## Title')
        ->and($decoded)->toContain('Some text after.');
});

it('preserves content outside of markdown component tags', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<div>
```php
echo "outside";
```
</div>
<x-slidewire::markdown>
```php
echo "inside";
```
</x-slidewire::markdown>';

    $result = $precompiler($template);

    expect($result)->toContain("```php\necho \"outside\";\n```");

    expect($result)->not->toContain('echo "inside"');
});

it('handles multiple code blocks inside a single markdown tag', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<x-slidewire::markdown>
```php
echo "first";
```

```bash
ls -la
```
</x-slidewire::markdown>';

    $result = $precompiler($template);

    expect($result)->not->toContain('echo "first"')
        ->and($result)->not->toContain('ls -la');

    $decoded = CodeBlockPrecompiler::decode($result);

    expect($decoded)->toContain('echo "first"')
        ->and($decoded)->toContain('ls -la');
});

it('handles multiple markdown component tags in a template', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<x-slidewire::markdown>
```php
echo "one";
```
</x-slidewire::markdown>
<div>gap</div>
<x-slidewire::markdown>
```php
echo "two";
```
</x-slidewire::markdown>';

    $result = $precompiler($template);

    expect($result)->not->toContain('echo "one"')
        ->and($result)->not->toContain('echo "two"')
        ->and($result)->toContain('gap');

    $decoded = CodeBlockPrecompiler::decode($result);

    expect($decoded)->toContain('echo "one"')
        ->and($decoded)->toContain('echo "two"');
});

it('protects Blade component syntax inside code blocks from compilation', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<x-slidewire::markdown>
```blade
<x-slidewire::deck theme="black">
    <x-slidewire::slide>Hello</x-slidewire::slide>
</x-slidewire::deck>
```
</x-slidewire::markdown>';

    $result = $precompiler($template);

    expect($result)->not->toContain('<x-slidewire::deck')
        ->and($result)->not->toContain('<x-slidewire::slide');

    $decoded = CodeBlockPrecompiler::decode($result);

    expect($decoded)->toContain('<x-slidewire::deck theme="black">')
        ->and($decoded)->toContain('<x-slidewire::slide>Hello</x-slidewire::slide>');
});

it('leaves markdown content without code blocks unchanged', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<x-slidewire::markdown>
## Just a heading

Some **bold** text and `inline code`.
</x-slidewire::markdown>';

    $result = $precompiler($template);

    expect($result)->toContain('## Just a heading')
        ->and($result)->toContain('Some **bold** text');
});

it('handles empty markdown component tags', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<x-slidewire::markdown></x-slidewire::markdown>';
    $result = $precompiler($template);

    expect($result)->toBe($template);
});

it('handles code blocks with no language specifier', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<x-slidewire::markdown>
```
plain text code
```
</x-slidewire::markdown>';

    $result = $precompiler($template);

    expect($result)->not->toContain('plain text code');

    $decoded = CodeBlockPrecompiler::decode($result);
    expect($decoded)->toContain('plain text code');
});

it('encodes slot content inside code component tags', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<x-slidewire::code language="php">
echo "hello";
</x-slidewire::code>';

    $result = $precompiler($template);

    expect($result)->not->toContain('echo "hello"')
        ->and($result)->toContain(CodeBlockPrecompiler::PLACEHOLDER_PREFIX)
        ->and($result)->toContain(CodeBlockPrecompiler::PLACEHOLDER_SUFFIX);
});

it('round-trips code component slot encode and decode correctly', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<x-slidewire::code language="php">
$deck = new Compiler();
$slides = $deck->compile("demo");
</x-slidewire::code>';

    $encoded = $precompiler($template);
    $decoded = CodeBlockPrecompiler::decode($encoded);

    expect($decoded)->toContain('$deck = new Compiler()')
        ->and($decoded)->toContain('$slides = $deck->compile("demo")');
});

it('protects Blade component syntax inside code component from compilation', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<x-slidewire::code language="blade">
<x-slidewire::deck theme="black">
    <x-slidewire::slide>Hello</x-slidewire::slide>
</x-slidewire::deck>
</x-slidewire::code>';

    $result = $precompiler($template);

    expect($result)->not->toContain('<x-slidewire::deck')
        ->and($result)->not->toContain('<x-slidewire::slide');

    $decoded = CodeBlockPrecompiler::decode($result);

    expect($decoded)->toContain('<x-slidewire::deck theme="black">')
        ->and($decoded)->toContain('<x-slidewire::slide>Hello</x-slidewire::slide>');
});

it('handles code component with attributes alongside markdown protection', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<x-slidewire::markdown>
```php
echo "in markdown";
```
</x-slidewire::markdown>
<x-slidewire::code language="php" theme="github-dark">
echo "in code";
</x-slidewire::code>';

    $result = $precompiler($template);

    expect($result)->not->toContain('echo "in markdown"')
        ->and($result)->not->toContain('echo "in code"');

    $decoded = CodeBlockPrecompiler::decode($result);

    expect($decoded)->toContain('echo "in markdown"')
        ->and($decoded)->toContain('echo "in code"');
});

it('handles empty code component tags', function (): void {
    $precompiler = new CodeBlockPrecompiler;

    $template = '<x-slidewire::code language="php"></x-slidewire::code>';
    $result = $precompiler($template);

    expect($result)->toBe($template);
});

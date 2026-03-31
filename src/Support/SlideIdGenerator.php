<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Support;

class SlideIdGenerator
{
    public function __construct(protected PresentationPathResolver $resolver) {}

    public function fromPath(string $path, int $hIndex, int $vIndex): string
    {
        $name = $this->normalizedName($path);

        if ($vIndex > 0) {
            return "{$name}-{$hIndex}-{$vIndex}";
        }

        return "{$name}-{$hIndex}";
    }

    protected function normalizedName(string $path): string
    {
        $normalizedPath = str_replace('\\', '/', $path);

        foreach ($this->resolver->roots() as $root) {
            $normalizedRoot = rtrim(str_replace('\\', '/', $root), '/');

            if (str_starts_with($normalizedPath, $normalizedRoot . '/')) {
                $relative = substr($normalizedPath, strlen($normalizedRoot) + 1);

                return $this->stripExtension($relative);
            }
        }

        return $this->stripExtension(basename($normalizedPath));
    }

    protected function stripExtension(string $path): string
    {
        $path = str_replace(['.blade.php', '.md'], '', $path);

        return str_replace(['/', '\\', '.'], '-', trim($path, '/'));
    }
}

<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;
use WendellAdriel\SlideWire\DTOs\RemoteConfig;
use WendellAdriel\SlideWire\Support\RemoteSessionManager;

class SlidewireRemoteCommand extends Command
{
    protected $signature = 'slidewire:remote
        {presentation : The presentation key (e.g. pitch)}
        {--ttl= : Session TTL using DSL format (e.g. 30m, 2h, 1d)}
        {--poll= : Viewer poll interval (e.g. 500ms, 2s)}';

    protected $description = 'Start a remote control session for a SlideWire presentation';

    public function handle(RemoteSessionManager $manager): int
    {
        $presentation = trim((string) $this->argument('presentation'), '/');
        $routeName = 'slidewire.' . str_replace('/', '.', $presentation);

        if (RouteFacade::getRoutes()->getByName($routeName) === null) {
            $this->error("No route found for presentation [{$presentation}]. Register it with Route::slidewire() first.");

            return self::FAILURE;
        }

        $config = RemoteConfig::resolved();
        $ttl = $this->option('ttl');
        $ttl = is_string($ttl) && $ttl !== '' ? $ttl : $config->ttl;

        $poll = $this->option('poll');
        $poll = is_string($poll) && $poll !== '' ? $poll : null;

        try {
            $session = $manager->create($presentation, $ttl, $poll);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $ttlSeconds = $session['ttl_seconds'];
        $ttlMinutes = (int) ceil($ttlSeconds / 60);

        $controllerUrl = URL::temporarySignedRoute(
            $routeName,
            now()->addSeconds($ttlSeconds),
            ['remote' => $session['key']],
        );

        $viewerUrl = route($routeName, ['remote' => $session['key']]);

        $this->newLine();
        $this->info("Remote session started for [{$presentation}]");
        $this->line("TTL: {$ttlMinutes} minutes");
        $this->line('Poll interval: ' . ($poll ?? $config->pollInterval));
        $this->newLine();
        $this->line('<fg=green>Controller:</>');
        $this->line($controllerUrl);
        $this->newLine();
        $this->line('<fg=cyan>Viewer:</>');
        $this->line($viewerUrl);
        $this->newLine();

        return self::SUCCESS;
    }
}

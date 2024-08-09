<?php

declare(strict_types=1);

namespace Tangoslee\PostScript;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;

class PostScriptServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerCommands();
        AboutCommand::add('Post Script', fn() => ['Version' => '1.0.0']);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/post_script.php', 'post_script'
        );
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ]);

        $this->commands([
            Commands\PostScript::class,
            Commands\RunPostScript::class,
            Commands\CreatePostScript::class,
            Commands\ShowPostScript::class,
            Commands\StatusPostScript::class,
        ]);
    }
}

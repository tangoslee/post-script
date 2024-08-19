<?php

declare(strict_types=1);

namespace Tangoslee\PostScript;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;

class PostScriptServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/post-script.php', 'post_scripts'
        );
        AboutCommand::add('Post Script', fn() => ['Version' => '0.5.0']);
    }

    public function register(): void
    {
        $this->registerCommands();
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishesMigrations([
            __DIR__ . '/../config' => base_path('config'),
        ]);

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ]);

        $this->publishesMigrations([
            __DIR__ . '/../post-scripts' => base_path('post-scripts'),
        ]);

        $this->commands([
            Commands\RunPostScript::class,
            Commands\CreatePostScript::class,
            Commands\ShowPostScript::class,
            Commands\StatusPostScript::class,
        ]);
    }
}

<?php

namespace Tangoslee\PostScript\Tests;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use Tangoslee\PostScript\PostScriptServiceProvider;

abstract class TestCase extends Orchestra
{
    use WithWorkbench;

    protected function setUp(): void
    {
        // Code before application created.
        $this->afterApplicationCreated(function () {
            // Code after application created.
        });

        $this->beforeApplicationDestroyed(function () {
            // Code before application destroyed.
        });

        parent::setUp();
        $this->refreshApplication();
        $this->artisan('vendor:publish', ['--provider' => PostScriptServiceProvider::class]);
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__ . '/../database/migrations/create_post_script_table.php'),
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(config('post-script.script_path'));
        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            PostScriptServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('post-scripts.table', 'post-scripts');
        $app['config']->set('script_path', base_path('post-scripts'));

        # Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}

<?php

namespace Tangoslee\PostScript\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tangoslee\PostScript\Tests\TestCase;

class BasicPostScriptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testConfigShouldBeSet(): void
    {
        $this->assertEquals('post_scripts', config('post-script.table'), 'Default table name');
        $this->assertEquals(base_path('post-scripts'), config('post-script.script_path'), 'Default script path');
    }

    public function testPostScriptTableShouldBeMigrated(): void
    {
        $this->assertFileExists(base_path('database/migrations/create_post_script_table.php'));
    }

    public function testCreatePostScriptShouldCreateNewPostScript(): void
    {
        $file = 'test_script';
        $this->artisan('make:post-script ' . $file);

        $this->assertDirectoryExists(base_path('post-scripts'));
        $this->assertFileExists(base_path(sprintf(
            'post-scripts/%s/%s/%s_%s.sh',
            Carbon::now()->format('Y'),
            Carbon::now()->format('m'),
            Carbon::now()->format('Y_m_d_His'),
            str_replace('-', '_', $file)
        )));
    }

    public function testStatusShouldDisplayUnhandledScript(): void
    {
        $this->createScript();

        $outputs = [
            '+------+-----------------------------+-------+----+',
            '| Ran? | Script                      | Batch | ID |',
            '+------+-----------------------------+-------+----+',
            '| No   | 2024_08_09_123456_script.sh |       |    |',
            '+------+-----------------------------+-------+----+',
        ];

        $command = $this->artisan('post-script:status')->assertOk();
        foreach ($outputs as $output) {
            $command->expectsOutput($output);
        }
    }

    public function testRunShouldExecuteScript(): void
    {
        $this->createScript();
        $batch = Carbon::now()->format('U');

        $command = $this->artisan('post-script:run')->assertOk();
        $outputs = [
            '2024_08_09_123456_script.sh',
            'echo "Hello"',
            '-------------------------------------',
        ];
        foreach ($outputs as $output) {
            $command->expectsOutput($output);
        }

        $command->run();
        $this->assertDatabaseHas('post_scripts', [
            'script' => '2024_08_09_123456_script.sh',
            'batch' => $batch,
        ]);
    }

    public function testReplayShouldExecuteScriptAgain(): void
    {
        $this->createScript();
        $batch = Carbon::now()->format('U');
        DB::table(config('post-script.table'))
            ->insert([
                'script' => '2024_08_09_123456_script.sh',
                'batch' => $batch,
            ]);
        $id = DB::getPdo()->lastInsertId();

        // Replay after a while
        Carbon::setTestNow('2024-08-10 01:12:12');
        $command = $this->artisan('post-script:run --replay ' . $id)
            ->assertOk();
        $command->run();
        $this->assertDatabaseHas('post_scripts', [
            'script' => '2024_08_09_123456_script.sh',
            'batch' => Carbon::now()->format('U'),
        ]);
    }

    private function createScript(): string
    {
        Carbon::setTestNow('2024-08-09 12:34:56');
        $path = base_path('post-scripts/2024/08/2024_08_09_123456_script.sh');
        File::makeDirectory(dirname($path), 0777, true, true);
        File::put($path, 'echo "Hello"');

        return $path;
    }
}

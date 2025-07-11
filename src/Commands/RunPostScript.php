<?php

namespace Tangoslee\PostScript\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class RunPostScript extends Command
{
    use PostScriptable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post-script:run
        { --replay= : run script again by id }
        { --force : Do not ask confirmation }
        { --local : Reset local environment before run the script }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run post script';

    private function runShellScript(string $script): void
    {
        if (! File::exists($script)) {
            Log::error(__METHOD__, [
                'error' => 'Script not found:' . $script,
            ]);
            return;
        }

        $cmd = "/bin/bash $script";
        $descriptorSpec = [STDIN, STDOUT, STDOUT];
        $proc = proc_open($cmd, $descriptorSpec, $pipes);
        proc_close($proc);
    }

    private function validateScriptBody(string $scriptBody): void
    {
        preg_match_all('/php artisan .*/im', $scriptBody, $matches, PREG_PATTERN_ORDER);
        $rows = array_filter($matches[0] ?? [], static function ($row) {
            return ! str_contains($row, '--force');
        });
        if (empty($rows)) {
            return;
        }

        $messages = array_merge([
            'Artisan command validation Warning',
            'Please add --force to end of these commands',
            '-------------------------------------------',
        ], $rows);
        throw new \InvalidArgumentException(implode(PHP_EOL, $messages));
    }


    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $isProduction = File::exists(base_path('.env')) && app()->isProduction();
            $localOptionOn = $this->option('local');

            $replayId = $this->option('replay');
            // requested by pk
            $needConfirm = $isProduction && ! $this->option('force');

            if ($needConfirm && ! $this->confirm('Are you sure you want to run the post script?')) {
                return;
            }

            if (! $isProduction && $localOptionOn && File::exists($this->initScriptPath())) {
                $this->runShellScript($this->initScriptPath());
            }

            $this->runScripts($replayId, $isProduction);

            if (! $isProduction && $localOptionOn) {
                $this->runShellScript($this->localScriptPath());
            } elseif ($localOptionOn) {
                $this->info('--local option is ignored on Production mode');
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    private function runScripts(?string $replayId, bool $isProduction): void
    {
        $scripts = $replayId
            ? $this->addScriptPath($this->fetchFromDB($replayId)->pluck('script')->toArray())
            : $this->fetchNeedToRunScripts();

        if (empty($scripts)) {
            $this->info('Nothing to run');

            return;
        }

        $batch = Carbon::now()->format('U');
        foreach ($scripts as $script) {
            $scriptBody = $this->readScript($script);
            $this->info(basename($script));
            $this->info($scriptBody);
            $this->line('-------------------------------------');

            if (! $isProduction && ! $this->option('force')) {
                try {
                    $this->validateScriptBody($scriptBody);
                } catch (\InvalidArgumentException $e) {
                    $this->error($e->getMessage());
                    if ($this->confirm('Do you want to exit to edit the command?', true)) {
                        return;
                    }
                }
            }

            // run script
            $this->runShellScript($script);

            // record db
            DB::table(config('post-script.table'))
                ->insert([
                    'script' => basename($script),
                    'batch' => $batch,
                ]);
        }
    }
}

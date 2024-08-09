<?php

namespace Tangoslee\PostScript\Commands;

use Illuminate\Console\Command;

class StatusPostScript extends Command
{
    use PostScriptable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post-script:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show status of the post script';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $header = ['Ran?', 'Script', 'Batch', 'ID'];
            $ranScript = $this->fetchFromDB()->reduce(function ($carry, $item) {
                $carry[] = [
                    '<info>Yes</info>',
                    $item->script,
                    $item->batch,
                    $item->id,
                ];

                return $carry;
            }, []);

            $readyScript = array_reduce($this->fetchNeedToRunScripts(), static function ($carry, $item) {
                $carry[] = [
                    '<fg=red>No</fg=red>',
                    basename($item),
                    '',
                    '',
                ];

                return $carry;
            }, []);

            $this->table($header, array_merge($ranScript, $readyScript));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}

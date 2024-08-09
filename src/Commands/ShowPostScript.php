<?php

namespace Tangoslee\PostScript\Commands;

use Illuminate\Console\Command;

class ShowPostScript extends Command
{
    use PostScriptable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post-script:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show post script';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $scripts = $this->fetchNeedToRunScripts();

            if (empty($scripts)) {
                $this->info('Nothing to show');
                return;
            }

            foreach ($scripts as $script) {
                $this->info($this->readScript($script));
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}

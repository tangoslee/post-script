<?php

namespace Tangoslee\PostScript\Commands;

use Illuminate\Console\Command;
use Tangoslee\PostScript\Helpers\StringHelper;

class CreatePostScript extends Command
{
    use PostScriptable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:post-script {script}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create post script skeleton';

    private function getTemplate(string $script): string
    {
        // phpcs:disable
        $body = <<<EOL
#!/bin/bash
# Write command to run

EOL;

        // phpcs:enable
        return $body;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $script = $this->argument('script');

        try {
            $script = StringHelper::combineWords($script);
            $fileName = $this->normalizeName($script);
            $path = $this->getScriptPath($fileName);
            $directory = dirname($path);

            $wildCardPath = preg_replace('/\/[\d]{4}_[\d]{2}_[\d]{2}_[\d]{6}_/', '/*_*_*_*_', $path);
            $exists = glob($wildCardPath);
            if ($exists) {
                throw new \InvalidArgumentException('The script name is already used: ' . ($exists[0] ?? $script));
            }

            if (! file_exists($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
            }


            if (! file_put_contents($path, $this->getTemplate($script))) {
                throw new \UnexpectedValueException("$path failed to create");
            }

            $this->info("$path created");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}

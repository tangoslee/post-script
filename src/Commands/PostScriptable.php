<?php

namespace Tangoslee\PostScript\Commands;


use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tangoslee\PostScript\Helpers\StringHelper;

trait PostScriptable
{
    private function checkTableExist(bool $throwPdoException = false): void
    {
        try {
            DB::table(config('post_script.table'))->exists();
        } catch (\PDOException $e) {
            if ($throwPdoException) {
                throw $e;
            }
            throw new \RuntimeException(config('post_script.table')
                . ' table not found. Please run php artisan migrate first');
        }
    }

    private function normalizeName(string $name): string
    {
        $prefix = Carbon::now()->format('Y_m_d_His');

        return sprintf('%s_%s', $prefix, StringHelper::extractWords($name));
    }

    private function getScriptPath(?string $script = ''): string
    {
        $path = config('post_script.script_path') . DIRECTORY_SEPARATOR . Carbon::now()->format('Y/m');

        if ($script) {
            $path .= DIRECTORY_SEPARATOR . $script . '.sh';
        }

        return $path;
    }

    private function addScriptPath(array $scripts): array
    {
        return array_map(static function ($script) {
            return sprintf(
                '%s/%s/%s',
                config('post_script.script_path'),
                preg_replace('/^([\d]{4})_([\d]{2})_.*/', '$1/$2', $script),
                $script
            );
        }, $scripts);
    }

    private function fetchFromDB(int $id = null): Collection
    {
        try {
            return DB::table(config('post_script.table'))
                ->when($id, function ($query) use ($id) {
                    $query->where('id', $id);
                })
                ->get();
        } catch (\PDOException $e) {
            return collect();
        }
    }

    private function fetchScriptsFromLocal(): array
    {
        $pattern = sprintf('%s/*/*/*.sh', config('post_script.script_path'));

        return glob($pattern) ?: [];
    }

    private function fetchNeedToRunScripts(): array
    {
        $dbScriptMap = $this->fetchFromDB()->pluck('batch', 'script');
        $localScripts = $this->fetchScriptsFromLocal();

        return array_filter($localScripts, static function ($script) use ($dbScriptMap) {
            return ! ($dbScriptMap[basename($script)] ?? null);
        });
    }

    private function readScript(string $script): string
    {
        return file_get_contents($script);
    }

    /**
     * TENC-287, --local option script
     */
    private function localScriptPath(): string
    {
        return config('post_script.script_path') . DIRECTORY_SEPARATOR . '/bin/local_script.sh';
    }
}

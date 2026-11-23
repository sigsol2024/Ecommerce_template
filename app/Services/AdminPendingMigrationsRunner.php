<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Runs pending Laravel migrations when staff open admin (Axion-style auto DB update).
 */
final class AdminPendingMigrationsRunner
{
    /**
     * @return array{ran: list<string>, output: string, skipped: bool, error: ?string}
     */
    public function run(): array
    {
        $empty = [
            'ran' => [],
            'output' => '',
            'skipped' => true,
            'error' => null,
        ];

        if ((string) config('app.admin_auto_migrate', '1') !== '1') {
            return $empty;
        }

        $lock = Cache::lock('admin_auto_migrate_lock_v1', 120);
        if (! $lock->get()) {
            return $empty;
        }

        try {
            $pending = $this->pendingMigrationNames();
            if ($pending === []) {
                return $empty;
            }

            $exit = Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());

            if ($exit !== 0) {
                Log::error('Admin auto-migrate failed', [
                    'exit' => $exit,
                    'output' => $output,
                    'pending' => $pending,
                ]);

                return [
                    'ran' => [],
                    'output' => $output,
                    'skipped' => false,
                    'error' => __('Database update failed. Check storage/logs or run: php artisan migrate --force'),
                ];
            }

            return [
                'ran' => $pending,
                'output' => $output,
                'skipped' => false,
                'error' => null,
            ];
        } catch (Throwable $e) {
            Log::error('Admin auto-migrate exception: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return [
                'ran' => [],
                'output' => '',
                'skipped' => false,
                'error' => __('Database update failed: :message', ['message' => $e->getMessage()]),
            ];
        } finally {
            $lock->release();
        }
    }

    /**
     * @return list<string>
     */
    private function pendingMigrationNames(): array
    {
        $migrator = app('migrator');
        $repository = $migrator->getRepository();

        if (! $repository->repositoryExists()) {
            $repository->createRepository();
        }

        $files = $migrator->getMigrationFiles([database_path('migrations')]);
        $ran = $repository->getRan();

        return array_values(array_diff(array_keys($files), $ran));
    }
}

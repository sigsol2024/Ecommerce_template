<?php

namespace App\Http\Middleware;

use App\Services\AdminPendingMigrationsRunner;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Axion-style: on staff/admin page loads, apply pending Laravel migrations.
 * Fail-open so admin UI still loads if migrate fails.
 */
class RunPendingMigrations
{
    public function __construct(
        private readonly AdminPendingMigrationsRunner $runner,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->isStaff()) {
            return $next($request);
        }

        // Avoid mutating schema mid-POST; run on reads (and first admin landing).
        if (! in_array(strtoupper($request->method()), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        try {
            $result = $this->runner->run();

            if (($result['error'] ?? null) !== null) {
                $request->session()->flash('admin_migrate_error', $result['error']);
            } elseif (($result['ran'] ?? []) !== []) {
                $count = count($result['ran']);
                $request->session()->flash(
                    'admin_migrate_status',
                    __('Database updated: :count migration(s) applied.', ['count' => $count])
                );
            }
        } catch (Throwable $e) {
            Log::warning('Admin auto-migrate middleware failed: '.$e->getMessage());
        }

        return $next($request);
    }
}

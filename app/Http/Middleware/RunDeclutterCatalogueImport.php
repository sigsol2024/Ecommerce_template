<?php

namespace App\Http\Middleware;

use App\Services\Declutter\DeclutterCatalogueImporter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Axion-style: on staff/admin page loads, create any missing declutter products (idempotent).
 * Never overwrites existing SKUs, fields, or images. Fail-open so admin UI always loads.
 */
class RunDeclutterCatalogueImport
{
    public function __construct(
        private readonly DeclutterCatalogueImporter $importer,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ((string) config('declutter.admin_auto_import', '1') !== '1') {
            return $next($request);
        }

        $user = $request->user();
        if (! $user || ! $user->isStaff()) {
            return $next($request);
        }

        // Avoid slowing POST/PUT mutations; import on reads (and the first admin landing).
        if (! in_array(strtoupper($request->method()), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        try {
            $result = $this->importer->importMissing($user, $user);
            if (($result['created'] ?? 0) > 0) {
                $request->session()->flash(
                    'status',
                    __('Declutter catalogue updated: :created product(s) imported (:skipped already present).', [
                        'created' => $result['created'],
                        'skipped' => $result['skipped'],
                    ])
                );
            }
            if (($result['errors'] ?? 0) > 0) {
                Log::warning('Declutter admin auto-import completed with errors', $result);
            }
        } catch (Throwable $e) {
            Log::warning('Declutter admin auto-import failed: '.$e->getMessage());
        }

        return $next($request);
    }
}

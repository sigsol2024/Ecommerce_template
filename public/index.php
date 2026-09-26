<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// #region agent log
$__dbgLog = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'debug-c150e5.log';
$__dbg = static function (string $hypothesisId, string $location, string $message, array $data = []) use ($__dbgLog): void {
    $line = json_encode([
        'sessionId' => 'c150e5',
        'timestamp' => (int) round(microtime(true) * 1000),
        'hypothesisId' => $hypothesisId,
        'location' => $location,
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_SLASHES);
    @file_put_contents($__dbgLog, $line."\n", FILE_APPEND);
};
$__dbg('H5', 'public/index.php:pre_maintenance', 'entry', ['php' => PHP_VERSION, 'uri' => $_SERVER['REQUEST_URI'] ?? null]);
register_shutdown_function(static function () use ($__dbg): void {
    $error = error_get_last();

    if ($error === null) {
        $__dbg('H4', 'public/index.php:shutdown', 'shutdown_clean', []);
        return;
    }

    $__dbg('H4', 'public/index.php:shutdown', 'shutdown_error', [
        'type' => $error['type'] ?? null,
        'message' => $error['message'] ?? null,
        'file' => $error['file'] ?? null,
        'line' => $error['line'] ?? null,
    ]);
});
$__dbg('H3', 'public/index.php:bootstrap_checks', 'paths', [
    'cwd' => getcwd(),
    'base' => dirname(__DIR__),
    'env_exists' => file_exists(dirname(__DIR__).'/.env'),
    'autoload_exists' => file_exists(__DIR__.'/../vendor/autoload.php'),
    'storage_logs_dir_exists' => is_dir(__DIR__.'/../storage/logs'),
    'storage_logs_dir_writable' => is_writable(__DIR__.'/../storage/logs'),
    'bootstrap_cache_dir_exists' => is_dir(__DIR__.'/../bootstrap/cache'),
    'bootstrap_cache_dir_writable' => is_writable(__DIR__.'/../bootstrap/cache'),
]);
// #endregion

// Determine if the application is in maintenance mode...
// #region agent log
try {
    if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
        require $maintenance;
        $__dbg('H1', 'public/index.php:maintenance', 'maintenance_loaded', []);
    } else {
        $__dbg('H1', 'public/index.php:maintenance', 'maintenance_absent', []);
    }
} catch (Throwable $e) {
    $__dbg('H1', 'public/index.php:maintenance_fail', 'maintenance_throw', ['class' => $e::class, 'msg' => $e->getMessage()]);
    throw $e;
}
// #endregion

// Register the Composer autoloader...
// #region agent log
try {
    require __DIR__.'/../vendor/autoload.php';
    $__dbg('H3', 'public/index.php:post_autoload', 'autoload_ok', []);
} catch (Throwable $e) {
    $__dbg('H3', 'public/index.php:autoload_fail', 'autoload_throw', ['class' => $e::class, 'msg' => $e->getMessage()]);
    throw $e;
}
// #endregion

// Bootstrap Laravel and handle the request...
/** @var Application $app */
// #region agent log
try {
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $__dbg('H1', 'public/index.php:post_bootstrap', 'app_created', ['class' => $app::class]);
} catch (Throwable $e) {
    $__dbg('H1', 'public/index.php:bootstrap_fail', 'bootstrap_throw', ['class' => $e::class, 'msg' => $e->getMessage()]);
    throw $e;
}
// #endregion

// #region agent log
try {
    $app->handleRequest(Request::capture());
    $__dbg('H2', 'public/index.php:post_handle', 'handle_ok', []);
} catch (Throwable $e) {
    $__dbg('H2', 'public/index.php:handle_fail', 'handle_throw', ['class' => $e::class, 'msg' => $e->getMessage()]);
    throw $e;
}
// #endregion

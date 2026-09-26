<?php

/**
 * Rewrites legacy vehicle_images paths in manual SQL patches to match DemoData.
 * Run from backend: php database/scripts/patch_manual_mysql_demo_images.php
 */

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$vehicles = \Database\Seeders\DemoData::vehicles();
$bySlug = [];
foreach ($vehicles as $row) {
    $slug = \Illuminate\Support\Str::slug($row['title']);
    $bySlug[$slug] = $row['images'];
}

$replaceBlock = function (string $slug, int $sort, string $url, string $tsPair) {
    $url = str_replace("'", "\\'", $url);

    return "SELECT v.id, '".$url."', ".$sort.", ".$tsPair."\nFROM `vehicles` v\nWHERE v.slug = '".$slug."';";
};

$patchPatterns = [
    // Legacy external and wp-exported paths in SQL
    "/SELECT v\\.id, 'assets\\/images\\/wp-uploads\\/[^']+', (\\d+), (?:@demo_ts, @demo_ts|'[^']+', '[^']+')\\s+FROM `vehicles` v\\s+WHERE v\\.slug = '([^']+)';/s",
    "/SELECT v\\.id, 'https:\\/\\/lh3\\.googleusercontent\\.com[^']+', (\\d+), (?:@demo_ts, @demo_ts|'[^']+', '[^']+')\\s+FROM `vehicles` v\\s+WHERE v\\.slug = '([^']+)';/s",
];

$patchFile = function (string $path, string $tsPair) use ($bySlug, $replaceBlock, $patchPatterns) {
    $sql = file_get_contents($path);
    foreach ($patchPatterns as $pattern) {
        $sql = preg_replace_callback($pattern, function ($m) use ($bySlug, $replaceBlock, $tsPair) {
            $sort = (int) $m[1];
            $slug = $m[2];
            if (! isset($bySlug[$slug])) {
                return $m[0];
            }
            $images = $bySlug[$slug];
            $url = $images[$sort] ?? $images[0];

            return $replaceBlock($slug, $sort, $url, $tsPair);
        }, $sql);
    }
    file_put_contents($path, $sql);
    fwrite(STDOUT, "Patched: {$path}\n");
};

$patchFile(dirname(__DIR__).'/manual_mysql_patches.sql', '@demo_ts, @demo_ts');

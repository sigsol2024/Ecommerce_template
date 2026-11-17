<?php

/**
 * Full audit: source notes vs declutter-products.php vs media files vs seeder safety.
 */
$root = dirname(__DIR__);
$sourceRoot = 'C:\\Users\\user pc\\Downloads\\drive-download-20261113T171840Z-1-001\\VCP Declutter - Items';
$dataPath = $root.'/database/seeders/data/declutter-products.php';
$seederPath = $root.'/database/seeders/DeclutterProductsSeeder.php';
$mediaRoot = $root.'/public/asset/images/media/declutter';

$categories = ['Appliances', 'Bathroom', 'Decorations', 'Electronics', 'Furniture', 'Generators', 'Kitchen', 'Lighting', 'Vehicles'];

function import_key(string $name): string
{
    $s = strtolower($name);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';

    return trim($s, '-');
}

function parse_note(string $path): array
{
    $kv = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        if (preg_match('/^([^:]+):\s*(.*)$/', $line, $m)) {
            $kv[trim($m[1])] = trim($m[2]);
        }
    }

    return $kv;
}

$issues = [];
$warns = [];
$info = [];

if (! is_file($dataPath)) {
    fwrite(STDERR, "FAIL: data file missing\n");
    exit(1);
}
if (! is_file($seederPath)) {
    fwrite(STDERR, "FAIL: seeder missing\n");
    exit(1);
}

$rows = require $dataPath;
if (! is_array($rows)) {
    fwrite(STDERR, "FAIL: data file not array\n");
    exit(1);
}

$byVin = [];
$byKey = [];
foreach ($rows as $i => $r) {
    $vin = (string) ($r['vin'] ?? '');
    $key = (string) ($r['import_key'] ?? '');
    if ($vin === '' || $key === '') {
        $issues[] = "Row {$i}: missing vin/import_key";
    }
    if (isset($byVin[$vin])) {
        $issues[] = "Duplicate vin in data: {$vin}";
    }
    if (isset($byKey[$key])) {
        $issues[] = "Duplicate import_key in data: {$key}";
    }
    $byVin[$vin] = $r;
    $byKey[$key] = $r;

    $expectedVin = 'VCP-DCLT-'.$key;
    if ($vin !== $expectedVin) {
        $issues[] = "Vin mismatch for {$key}: got {$vin}, expected {$expectedVin}";
    }
    $expectedSlug = 'vcp-declutter-'.$key;
    if (($r['slug'] ?? '') !== $expectedSlug) {
        $warns[] = "Slug atypical for {$key}: ".($r['slug'] ?? '');
    }
}

// Build source inventory
$sourceItems = [];
foreach ($categories as $cat) {
    $catDir = $sourceRoot.DIRECTORY_SEPARATOR.$cat;
    if (! is_dir($catDir)) {
        $issues[] = "Source category folder missing: {$cat}";

        continue;
    }
    foreach (scandir($catDir) ?: [] as $folder) {
        if ($folder === '.' || $folder === '..') {
            continue;
        }
        $full = $catDir.DIRECTORY_SEPARATOR.$folder;
        if (! is_dir($full)) {
            continue;
        }
        $key = import_key($folder);
        $note = is_file($full.'/item-note.txt') ? parse_note($full.'/item-note.txt') : [];
        $jpgs = glob($full.DIRECTORY_SEPARATOR.'*.jpg') ?: [];
        sort($jpgs);
        $sourceItems[$key] = [
            'folder' => $folder,
            'category' => $cat,
            'note' => $note,
            'jpgs' => array_map('basename', $jpgs),
            'full' => $full,
        ];
    }
}

$info[] = 'data_rows='.count($rows);
$info[] = 'source_items='.count($sourceItems);

foreach ($sourceItems as $key => $src) {
    if (! isset($byKey[$key])) {
        $issues[] = "Source item missing from data file: {$src['category']}/{$src['folder']} (key={$key})";

        continue;
    }
    $row = $byKey[$key];
    $note = $src['note'];
    $titleExpected = $note['Item'] ?? $src['folder'];
    if (($row['title'] ?? '') !== $titleExpected) {
        $issues[] = "Title mismatch {$key}: data='{$row['title']}' source='{$titleExpected}'";
    }
    if (($row['category'] ?? '') !== $src['category']) {
        $issues[] = "Category mismatch {$key}: data='{$row['category']}' source='{$src['category']}'";
    }

    $priceRaw = $note['Unit price'] ?? '';
    $qtyRaw = $note['Quantity'] ?? '';
    $priceExpected = preg_match('/^\d+$/', $priceRaw) ? (int) $priceRaw : 0;
    $stockExpected = preg_match('/^\d+$/', $qtyRaw) ? (int) $qtyRaw : 0;
    if ((int) ($row['price'] ?? -1) !== $priceExpected) {
        $issues[] = "Price mismatch {$key}: data={$row['price']} expected={$priceExpected} (raw='{$priceRaw}')";
    }
    if ((int) ($row['stock'] ?? -1) !== $stockExpected) {
        $issues[] = "Stock mismatch {$key}: data={$row['stock']} expected={$stockExpected} (raw='{$qtyRaw}')";
    }

    $descRaw = $note['Description'] ?? '';
    $descExpected = ($descRaw !== '' && $descRaw !== '(none)') ? $descRaw : ('VCP declutter item: '.$titleExpected);
    if (($row['description'] ?? '') !== $descExpected) {
        $issues[] = "Description mismatch {$key}: data='{$row['description']}' expected='{$descExpected}'";
    }

    // Images: source vs data vs media disk
    $dataImgs = $row['image_files'] ?? [];
    if (! is_array($dataImgs)) {
        $issues[] = "image_files not array for {$key}";

        continue;
    }
    if (count($dataImgs) !== count($src['jpgs'])) {
        $issues[] = "Image count mismatch {$key}: data=".count($dataImgs).' source='.count($src['jpgs']);
    }
    foreach ($src['jpgs'] as $idx => $jpgName) {
        $expectedRel = 'asset/images/media/declutter/'.$key.'/'.$jpgName;
        if (! isset($dataImgs[$idx]) || $dataImgs[$idx] !== $expectedRel) {
            // allow any order — check membership
            if (! in_array($expectedRel, $dataImgs, true)) {
                $issues[] = "Data missing image path {$expectedRel}";
            }
        }
        $abs = $root.'/public/'.$expectedRel;
        if (! is_file($abs)) {
            $issues[] = "Media file missing on disk: {$expectedRel}";
        } else {
            $srcFile = $src['full'].DIRECTORY_SEPARATOR.$jpgName;
            if (is_file($srcFile) && filesize($abs) !== filesize($srcFile)) {
                $issues[] = "Media size mismatch {$expectedRel}: disk=".filesize($abs).' source='.filesize($srcFile);
            }
        }
    }
}

foreach ($byKey as $key => $row) {
    if (! isset($sourceItems[$key])) {
        $issues[] = "Data has orphan import_key not in source: {$key}";
    }
}

// Orphan media folders
if (is_dir($mediaRoot)) {
    foreach (scandir($mediaRoot) ?: [] as $dir) {
        if ($dir === '.' || $dir === '..') {
            continue;
        }
        $full = $mediaRoot.'/'.$dir;
        if (! is_dir($full)) {
            continue;
        }
        if (! isset($byKey[$dir])) {
            $warns[] = "Orphan media folder not in data: {$dir}";
        }
        foreach (glob($full.'/*.jpg') ?: [] as $jpg) {
            $rel = 'asset/images/media/declutter/'.$dir.'/'.basename($jpg);
            $listed = false;
            if (isset($byKey[$dir])) {
                $listed = in_array($rel, $byKey[$dir]['image_files'] ?? [], true);
            }
            if (! $listed && isset($byKey[$dir])) {
                $warns[] = "Media jpg not listed in data: {$rel}";
            }
        }
    }
}

// Seeder static review
$seederSrc = file_get_contents($seederPath) ?: '';
$checks = [
    'create-only (no updateOrCreate)' => ! str_contains($seederSrc, 'updateOrCreate'),
    'match by vin' => str_contains($seederSrc, "where('vin'"),
    'skip existing' => str_contains($seederSrc, 'skipped'),
    'DECLUTTER_SEED_ALLOW' => str_contains($seederSrc, 'DECLUTTER_SEED_ALLOW'),
    'DECLUTTER_SEED_CONFIRM_DB' => str_contains($seederSrc, 'DECLUTTER_SEED_CONFIRM_DB'),
    'DECLUTTER_SEED_OWNER_EMAIL' => str_contains($seederSrc, 'DECLUTTER_SEED_OWNER_EMAIL'),
    'no DemoData' => ! str_contains($seederSrc, 'DemoData'),
    'no firstOrCreate users' => ! str_contains($seederSrc, 'firstOrCreate'),
    'images only after create' => preg_match('/Vehicle::query\(\)->create\(/', $seederSrc)
        && preg_match('/VehicleImage::query\(\)->create\(/', $seederSrc)
        && ! preg_match('/vehicle_images.*delete|images\(\)->delete/i', $seederSrc),
];

foreach ($checks as $label => $ok) {
    if (! $ok) {
        $issues[] = "Seeder check failed: {$label}";
    } else {
        $info[] = "seeder_ok: {$label}";
    }
}

// DatabaseSeeder should NOT auto-call declutter
$dbSeeder = $root.'/database/seeders/DatabaseSeeder.php';
if (is_file($dbSeeder)) {
    $dbSrc = file_get_contents($dbSeeder) ?: '';
    if (str_contains($dbSrc, 'DeclutterProductsSeeder')) {
        $issues[] = 'DatabaseSeeder auto-calls DeclutterProductsSeeder (should be explicit only)';
    } else {
        $info[] = 'DatabaseSeeder does not auto-run declutter seeder';
    }
}

// Price zero list (expected)
$priceZero = [];
foreach ($rows as $r) {
    if ((int) $r['price'] === 0) {
        $priceZero[] = $r['category'].'/'.$r['title'];
    }
}
$zeroPhoto = [];
foreach ($rows as $r) {
    if (($r['image_files'] ?? []) === []) {
        $zeroPhoto[] = $r['category'].'/'.$r['title'];
    }
}

$realDesc = [];
$high = [];
$catCounts = [];
foreach ($rows as $r) {
    $catCounts[$r['category']] = ($catCounts[$r['category']] ?? 0) + 1;
    if (! str_starts_with((string) $r['description'], 'VCP declutter item:')) {
        $realDesc[] = $r['title'].' | '.$r['description'].' | '.$r['price'];
    }
    if ((int) $r['price'] >= 1000000) {
        $high[] = $r['category'].'/'.$r['title'].' | NGN '.number_format((int) $r['price']).' | imgs='.count($r['image_files'] ?? []);
    }
}

echo "=== AUDIT SUMMARY ===\n";
foreach ($info as $line) {
    echo "INFO  {$line}\n";
}
ksort($catCounts);
foreach ($catCounts as $k => $v) {
    echo "INFO  category_count: {$k}={$v}\n";
}
echo 'price_zero_count='.count($priceZero)."\n";
foreach ($priceZero as $p) {
    echo "INFO  price_zero: {$p}\n";
}
echo 'zero_photo_count='.count($zeroPhoto)."\n";
foreach ($zeroPhoto as $p) {
    echo "INFO  zero_photo: {$p}\n";
}
echo 'real_description_count='.count($realDesc)."\n";
foreach ($realDesc as $p) {
    echo "INFO  real_description: {$p}\n";
}
echo 'high_value_count='.count($high)."\n";
foreach ($high as $p) {
    echo "INFO  high_value: {$p}\n";
}
foreach ($warns as $w) {
    echo "WARN  {$w}\n";
}
foreach ($issues as $i) {
    echo "ISSUE {$i}\n";
}
echo 'issues='.count($issues).' warns='.count($warns)."\n";
exit(count($issues) > 0 ? 1 : 0);

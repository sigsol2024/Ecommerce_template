<?php

/**
 * Verifies declutter data file + media assets, and optionally exercises the seeder
 * against a temporary SQLite database (never the production .env DB).
 *
 * Usage:
 *   php scripts/verify-declutter-import.php
 *   php scripts/verify-declutter-import.php --with-sqlite-seed
 */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$rows = require $root.'/database/seeders/data/declutter-products.php';
if (! is_array($rows) || $rows === []) {
    fwrite(STDERR, "FAIL: declutter-products.php empty\n");
    exit(1);
}

$missing = 0;
$ok = 0;
$zero = 0;
$vins = [];
foreach ($rows as $r) {
    $vin = (string) ($r['vin'] ?? '');
    if ($vin === '' || isset($vins[$vin])) {
        fwrite(STDERR, "FAIL: duplicate or empty vin: {$vin}\n");
        exit(1);
    }
    $vins[$vin] = true;

    $imgs = $r['image_files'] ?? [];
    if (! is_array($imgs) || $imgs === []) {
        $zero++;

        continue;
    }
    foreach ($imgs as $p) {
        $abs = $root.'/public/'.ltrim((string) $p, '/');
        if (! is_file($abs)) {
            fwrite(STDERR, "MISSING {$p}\n");
            $missing++;
        } else {
            $ok++;
        }
    }
}

echo 'products='.count($rows)." images_ok={$ok} images_missing={$missing} zero_photo_products={$zero}\n";
if ($missing > 0) {
    exit(1);
}

$withSqlite = in_array('--with-sqlite-seed', $argv, true);
if (! $withSqlite) {
    echo "Asset check passed. Run with --with-sqlite-seed to exercise create-only seeder on temp SQLite.\n";
    exit(0);
}

// --- Isolated SQLite seed exercise (does not use .env DB) ---
$tmpDir = $root.'/storage/app/declutter-verify';
if (! is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}
$dbPath = $tmpDir.'/verify.sqlite';
if (is_file($dbPath)) {
    unlink($dbPath);
}
touch($dbPath);

$app = require $root.'/bootstrap/app.php';
/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config([
    'database.default' => 'sqlite',
    'database.connections.sqlite.database' => $dbPath,
    'app.env' => 'local',
]);

Illuminate\Support\Facades\DB::purge();
Illuminate\Support\Facades\DB::reconnect('sqlite');

// Minimal schema for the seeder
Illuminate\Support\Facades\Schema::create('users', function (Illuminate\Database\Schema\Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->boolean('is_super_admin')->default(false);
    $table->timestamps();
});

Illuminate\Support\Facades\Schema::create('roles', function (Illuminate\Database\Schema\Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('guard_name')->default('web');
    $table->timestamps();
});

Illuminate\Support\Facades\Schema::create('model_has_roles', function (Illuminate\Database\Schema\Blueprint $table) {
    $table->unsignedBigInteger('role_id');
    $table->string('model_type');
    $table->unsignedBigInteger('model_id');
    $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
});

Illuminate\Support\Facades\Schema::create('listing_option_categories', function (Illuminate\Database\Schema\Blueprint $table) {
    $table->id();
    $table->string('slug')->unique();
    $table->string('label');
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();
});

Illuminate\Support\Facades\Schema::create('listing_options', function (Illuminate\Database\Schema\Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('category_id');
    $table->unsignedBigInteger('parent_id')->nullable();
    $table->string('value');
    $table->string('logo_path')->nullable();
    $table->string('flag_emoji')->nullable();
    $table->unsignedInteger('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Illuminate\Support\Facades\Schema::create('vehicles', function (Illuminate\Database\Schema\Blueprint $table) {
    $table->id();
    $table->foreignId('user_id');
    $table->string('title');
    $table->string('slug')->unique();
    $table->string('status')->default('draft');
    $table->unsignedInteger('price')->default(0);
    $table->unsignedInteger('stock')->default(0);
    $table->string('vin')->nullable();
    $table->json('features')->nullable();
    $table->text('description')->nullable();
    $table->text('overview')->nullable();
    $table->boolean('is_special')->default(false);
    $table->timestamp('submitted_at')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->unsignedBigInteger('approved_by')->nullable();
    $table->text('rejection_reason')->nullable();
    $table->unsignedBigInteger('product_category_listing_option_id')->nullable();
    $table->timestamps();
});

Illuminate\Support\Facades\Schema::create('vehicle_images', function (Illuminate\Database\Schema\Blueprint $table) {
    $table->id();
    $table->foreignId('vehicle_id');
    $table->text('path');
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();
});

$roleId = Illuminate\Support\Facades\DB::table('roles')->insertGetId([
    'name' => 'admin',
    'guard_name' => 'web',
    'created_at' => now(),
    'updated_at' => now(),
]);

$user = App\Models\User::query()->create([
    'name' => 'Declutter Verify Admin',
    'email' => 'declutter-verify@example.test',
    'password' => 'password',
    'email_verified_at' => now(),
]);

Illuminate\Support\Facades\DB::table('model_has_roles')->insert([
    'role_id' => $roleId,
    'model_type' => App\Models\User::class,
    'model_id' => $user->id,
]);

config([
    'declutter.allow' => '1',
    'declutter.allow_local' => '1',
    'declutter.confirm_db' => $dbPath,
    'declutter.owner_email' => 'declutter-verify@example.test',
    'declutter.approver_email' => '',
]);

$seeder = new Database\Seeders\DeclutterProductsSeeder;

echo "=== First seed (expect creates) ===\n";
$seeder->run();
$afterFirst = App\Models\Vehicle::query()->count();
$imagesFirst = App\Models\VehicleImage::query()->count();
echo "vehicles_after_first={$afterFirst} images_after_first={$imagesFirst}\n";

// Mutate one imported product + image to prove re-run preserves admin edits
$sample = App\Models\Vehicle::query()->where('vin', 'VCP-DCLT-handheld-dryer')->first();
if (! $sample) {
    fwrite(STDERR, "FAIL: sample product missing after first seed\n");
    exit(1);
}
$sample->price = 999;
$sample->description = 'ADMIN EDITED';
$sample->save();
$imgCountBefore = $sample->images()->count();
$sample->images()->delete();
$sample->images()->create(['path' => 'asset/images/media/custom-admin.jpg', 'sort_order' => 0]);

echo "=== Second seed (expect skips; preserve edits) ===\n";
$seeder->run();

$sample->refresh();
$sample->load('images');
if ((int) $sample->price !== 999 || $sample->description !== 'ADMIN EDITED') {
    fwrite(STDERR, "FAIL: admin field edits were overwritten\n");
    exit(1);
}
if ($sample->images->count() !== 1 || $sample->images->first()->path !== 'asset/images/media/custom-admin.jpg') {
    fwrite(STDERR, "FAIL: admin images were reset\n");
    exit(1);
}

$afterSecond = App\Models\Vehicle::query()->count();
if ($afterSecond !== $afterFirst) {
    fwrite(STDERR, "FAIL: vehicle count changed on re-run ({$afterFirst} -> {$afterSecond})\n");
    exit(1);
}

echo "PASS: create-only + skip-on-rerun + image preservation confirmed on temp SQLite.\n";
echo "imgCountBefore_was={$imgCountBefore}\n";
exit(0);

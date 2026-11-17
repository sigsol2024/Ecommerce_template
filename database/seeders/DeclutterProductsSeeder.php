<?php

namespace Database\Seeders;

use App\Models\ListingOption;
use App\Models\ListingOptionCategory;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * One-shot create-only import of VCP declutter catalogue items.
 *
 * - Matches existing rows by vin (SKU): VCP-DCLT-{import_key}
 * - Skips products that already exist (never overwrites admin edits or images)
 * - Attaches vehicle_images only on create
 * - Never creates demo users; requires explicit owner email + DB confirmation env vars
 *
 * Not registered in DatabaseSeeder — run explicitly:
 *   php artisan db:seed --class=Database\\Seeders\\DeclutterProductsSeeder --force
 *
 * See database/README.md (Declutter catalogue import).
 */
class DeclutterProductsSeeder extends Seeder
{
    /** @var list<string> */
    private const CATEGORIES = [
        'Appliances',
        'Bathroom',
        'Decorations',
        'Electronics',
        'Furniture',
        'Generators',
        'Kitchen',
        'Lighting',
        'Vehicles',
    ];

    public function run(): void
    {
        $products = $this->declutterProducts();
        if ($products === []) {
            $this->command?->error('No declutter products found at database/seeders/data/declutter-products.php');

            return;
        }

        $this->preflight();

        $owner = $this->resolveOwner();
        $approver = $this->resolveApprover($owner);

        $this->ensureProductCategoryDimension();
        $this->ensureDeclutterCategories();
        $categoryLookup = $this->productCategoryLookup();

        $hasStock = Schema::hasColumn('vehicles', 'stock');
        $hasCategory = Schema::hasColumn('vehicles', 'product_category_listing_option_id');
        $hasOverview = Schema::hasColumn('vehicles', 'overview');

        $created = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($products as $product) {
            $vin = trim((string) ($product['vin'] ?? ''));
            $importKey = trim((string) ($product['import_key'] ?? ''));
            if ($vin === '' || $importKey === '') {
                $this->command?->warn('Skipping row with missing vin/import_key.');
                $errors++;

                continue;
            }

            if (Vehicle::query()->where('vin', $vin)->exists()) {
                $skipped++;

                continue;
            }

            try {
                $slug = $this->uniqueSlug((string) ($product['slug'] ?? ('vcp-declutter-'.$importKey)));
                $categoryName = (string) ($product['category'] ?? '');
                $categoryId = $categoryLookup[strtolower($categoryName)] ?? null;

                $payload = [
                    'user_id' => $owner->id,
                    'title' => (string) ($product['title'] ?? $importKey),
                    'slug' => $slug,
                    'status' => 'approved',
                    'price' => (int) ($product['price'] ?? 0),
                    'features' => null,
                    'description' => (string) ($product['description'] ?? ''),
                    'vin' => $vin,
                    'submitted_at' => now(),
                    'approved_at' => now(),
                    'approved_by' => $approver->id,
                ];

                if ($hasStock) {
                    $payload['stock'] = (int) ($product['stock'] ?? 0);
                }

                if ($hasOverview) {
                    $payload['overview'] = (string) ($product['overview'] ?? $payload['title']);
                }

                if ($hasCategory) {
                    $payload['product_category_listing_option_id'] = $categoryId;
                }

                $vehicle = Vehicle::query()->create($payload);

                $imageFiles = $product['image_files'] ?? [];
                if (is_array($imageFiles)) {
                    $sort = 0;
                    foreach ($imageFiles as $path) {
                        $path = ltrim((string) $path, '/');
                        if ($path === '') {
                            continue;
                        }
                        VehicleImage::query()->create([
                            'vehicle_id' => $vehicle->id,
                            'path' => $path,
                            'sort_order' => $sort,
                        ]);
                        $sort++;
                    }
                }

                $created++;
            } catch (\Throwable $e) {
                $errors++;
                $this->command?->error("Failed importing {$vin}: ".$e->getMessage());
            }
        }

        $this->command?->info(
            "Declutter import complete: {$created} created, {$skipped} skipped (already present), {$errors} errors, "
            .count($products).' in data file.'
        );
    }

    private function preflight(): void
    {
        // Use config/declutter.php so gates still work when `config:cache` is enabled.
        $allow = (string) config('declutter.allow', '0');
        if ($allow !== '1') {
            throw new RuntimeException(
                'Declutter seed refused: set DECLUTTER_SEED_ALLOW=1 after backing up the database. See database/README.md.'
            );
        }

        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");
        $appEnv = (string) config('app.env');

        $this->command?->info("Declutter seed preflight: APP_ENV={$appEnv}, connection={$connection}, database={$database}");

        $confirmDb = (string) config('declutter.confirm_db', '');
        if ($confirmDb === '' || $confirmDb !== $database) {
            throw new RuntimeException(
                "Declutter seed refused: DECLUTTER_SEED_CONFIRM_DB must exactly match the current database name \"{$database}\"."
            );
        }

        if (in_array($appEnv, ['local', 'testing'], true) && (string) config('declutter.allow_local', '0') !== '1') {
            throw new RuntimeException(
                'Declutter seed refused on local/testing: set DECLUTTER_SEED_ALLOW_LOCAL=1 to proceed deliberately.'
            );
        }

        $ownerEmail = trim((string) config('declutter.owner_email', ''));
        if ($ownerEmail === '') {
            throw new RuntimeException(
                'Declutter seed refused: DECLUTTER_SEED_OWNER_EMAIL must be set to an existing user email.'
            );
        }
    }

    private function resolveOwner(): User
    {
        $email = trim((string) config('declutter.owner_email', ''));
        $owner = User::query()->where('email', $email)->first();
        if (! $owner) {
            throw new RuntimeException("Declutter seed refused: owner user not found for email \"{$email}\".");
        }

        return $owner;
    }

    private function resolveApprover(User $owner): User
    {
        $email = trim((string) config('declutter.approver_email', ''));
        if ($email === '') {
            if ($owner->isStaff()) {
                return $owner;
            }

            throw new RuntimeException(
                'Declutter seed refused: owner is not staff. Set DECLUTTER_SEED_APPROVER_EMAIL to an existing staff user.'
            );
        }

        $approver = User::query()->where('email', $email)->first();
        if (! $approver) {
            throw new RuntimeException("Declutter seed refused: approver user not found for email \"{$email}\".");
        }

        if (! $approver->isStaff()) {
            throw new RuntimeException(
                "Declutter seed refused: approver \"{$email}\" is not staff (admin/editor)."
            );
        }

        return $approver;
    }

    private function ensureProductCategoryDimension(): void
    {
        if (! Schema::hasTable('listing_option_categories')) {
            throw new RuntimeException('listing_option_categories table missing.');
        }

        $exists = ListingOptionCategory::query()->where('slug', 'product_category')->exists();
        if ($exists) {
            return;
        }

        ListingOptionCategory::query()->create([
            'slug' => 'product_category',
            'label' => 'Product category',
            'sort_order' => 5,
        ]);
    }

    private function ensureDeclutterCategories(): void
    {
        if (! Schema::hasTable('listing_options') || ! Schema::hasTable('listing_option_categories')) {
            throw new RuntimeException('listing_options tables missing.');
        }

        $categoryId = (int) ListingOptionCategory::query()->where('slug', 'product_category')->value('id');
        if ($categoryId === 0) {
            throw new RuntimeException('product_category dimension missing.');
        }

        $lookup = $this->productCategoryLookup();
        $maxSort = (int) ListingOption::query()
            ->where('category_id', $categoryId)
            ->whereNull('parent_id')
            ->max('sort_order');

        foreach (self::CATEGORIES as $name) {
            $key = strtolower($name);
            if (isset($lookup[$key])) {
                continue;
            }

            $maxSort += 10;
            ListingOption::query()->create([
                'category_id' => $categoryId,
                'parent_id' => null,
                'value' => $name,
                'sort_order' => $maxSort,
                'is_active' => true,
            ]);
            $this->command?->info("Created product category: {$name}");
        }
    }

    /**
     * @return array<string, int>
     */
    private function productCategoryLookup(): array
    {
        $categoryId = (int) ListingOptionCategory::query()->where('slug', 'product_category')->value('id');
        if ($categoryId === 0) {
            return [];
        }

        return ListingOption::query()
            ->where('category_id', $categoryId)
            ->whereNull('parent_id')
            ->pluck('id', 'value')
            ->mapWithKeys(fn ($id, $value) => [strtolower((string) $value) => (int) $id])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function declutterProducts(): array
    {
        $path = database_path('seeders/data/declutter-products.php');
        if (! is_file($path)) {
            return [];
        }

        $data = require $path;

        return is_array($data) ? $data : [];
    }

    private function uniqueSlug(string $preferred): string
    {
        $base = Str::slug($preferred);
        if ($base === '') {
            $base = 'vcp-declutter-item';
        }

        $slug = $base;
        $i = 2;
        while (Vehicle::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}

<?php

namespace App\Services\Declutter;

use App\Models\ListingOption;
use App\Models\ListingOptionCategory;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Create-only VCP declutter catalogue import.
 * Skips existing SKUs (vin); never overwrites admin edits or images.
 */
class DeclutterCatalogueImporter
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

    /**
     * @return array{created: int, skipped: int, errors: int, total: int}
     */
    public function importMissing(User $owner, User $approver): array
    {
        $products = $this->products();
        $total = count($products);
        if ($total === 0) {
            return ['created' => 0, 'skipped' => 0, 'errors' => 0, 'total' => 0];
        }

        $vins = [];
        foreach ($products as $product) {
            $vin = trim((string) ($product['vin'] ?? ''));
            if ($vin !== '') {
                $vins[] = $vin;
            }
        }

        $existing = Vehicle::query()
            ->whereIn('vin', $vins)
            ->pluck('vin')
            ->all();
        $existingMap = array_fill_keys($existing, true);

        $missingCount = 0;
        foreach ($vins as $vin) {
            if (! isset($existingMap[$vin])) {
                $missingCount++;
            }
        }

        if ($missingCount === 0) {
            return ['created' => 0, 'skipped' => $total, 'errors' => 0, 'total' => $total];
        }

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
                $errors++;

                continue;
            }

            if (isset($existingMap[$vin]) || Vehicle::query()->where('vin', $vin)->exists()) {
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

                $existingMap[$vin] = true;
                $created++;
            } catch (Throwable $e) {
                $errors++;
                Log::warning('Declutter import row failed', [
                    'vin' => $vin,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'total' => $total,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function products(): array
    {
        $path = database_path('seeders/data/declutter-products.php');
        if (! is_file($path)) {
            return [];
        }

        $data = require $path;

        return is_array($data) ? $data : [];
    }

    private function ensureProductCategoryDimension(): void
    {
        if (! Schema::hasTable('listing_option_categories')) {
            return;
        }

        if (ListingOptionCategory::query()->where('slug', 'product_category')->exists()) {
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
            return;
        }

        $categoryId = (int) ListingOptionCategory::query()->where('slug', 'product_category')->value('id');
        if ($categoryId === 0) {
            return;
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
        }
    }

    /**
     * @return array<string, int>
     */
    private function productCategoryLookup(): array
    {
        if (! Schema::hasTable('listing_options') || ! Schema::hasTable('listing_option_categories')) {
            return [];
        }

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

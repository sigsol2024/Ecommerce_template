<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /** @var list<string> */
    public const ALL = [
        'dashboard.view',
        'products.manage',
        'categories.manage',
        'variants.manage',
        'orders.manage',
        'analytics.view',
        'customers.view',
        'customers.manage',
        'media.manage',
        'pages.manage',
        'settings.manage',
        'staff.manage',
        'audit.view',
    ];

    /** @var list<string> */
    public const EDITOR = [
        'products.manage',
        'categories.manage',
        'variants.manage',
        'orders.manage',
        'analytics.view',
        'customers.view',
        'media.manage',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        foreach (self::ALL as $name) {
            Permission::findOrCreate($name, $guard);
        }

        // Spatie caches the permission list; refresh after creates so syncPermissions can resolve names.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Role::findOrCreate('admin', $guard);
        $editor = Role::findOrCreate('editor', $guard);

        $admin->syncPermissions(self::ALL);
        $editor->syncPermissions(self::EDITOR);
    }
}

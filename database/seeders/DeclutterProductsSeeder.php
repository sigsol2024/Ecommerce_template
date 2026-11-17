<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Declutter\DeclutterCatalogueImporter;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * CLI create-only import of VCP declutter catalogue items.
 *
 * Prefer admin-page auto-import (RunDeclutterCatalogueImport) on production hosts
 * without Artisan. This seeder remains for explicit CLI runs with safety gates.
 *
 *   php artisan db:seed --class=Database\\Seeders\\DeclutterProductsSeeder --force
 *
 * See database/README.md (Declutter catalogue import).
 */
class DeclutterProductsSeeder extends Seeder
{
    public function run(): void
    {
        $this->preflight();

        $owner = $this->resolveOwner();
        $approver = $this->resolveApprover($owner);

        /** @var DeclutterCatalogueImporter $importer */
        $importer = app(DeclutterCatalogueImporter::class);
        $result = $importer->importMissing($owner, $approver);

        $this->command?->info(
            "Declutter import complete: {$result['created']} created, {$result['skipped']} skipped (already present), "
            ."{$result['errors']} errors, {$result['total']} in data file."
        );
    }

    private function preflight(): void
    {
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
}

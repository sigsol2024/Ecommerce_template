<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMediaBulkDestroyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->seed(PermissionsSeeder::class);
    }

    public function test_bulk_destroy_route_is_not_captured_as_media_id(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $media = Media::query()->create([
            'filename' => 'demo.webp',
            'original_name' => 'demo.webp',
            'file_path' => 'uploads/demo.webp',
            'file_type' => 'image/webp',
            'file_size' => 100,
            'uploaded_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.media.bulk-destroy'), ['ids' => [$media->id]])
            ->assertRedirect(route('admin.media.index'));

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertSame(url('/admin/media-bulk-destroy'), route('admin.media.bulk-destroy'));
    }
}

<?php

namespace Database\Seeders;

use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $dir = public_path('asset/images/media');
        if (! is_dir($dir)) {
            return;
        }

        foreach (File::files($dir) as $file) {
            $name = $file->getFilename();
            Media::query()->updateOrCreate(
                ['file_path' => 'asset/images/media/'.$name],
                [
                    'filename' => $name,
                    'original_name' => $name,
                    'file_type' => strtolower($file->getExtension()),
                    'file_size' => (int) $file->getSize(),
                ]
            );
        }
    }
}

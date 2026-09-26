<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use Illuminate\Database\Seeder;

class CmsPagesSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            'home' => [
                'title' => 'Home',
                'meta_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'file' => 'cms-home-body.html',
            ],
            'about' => [
                'title' => 'About Us',
                'meta_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'file' => 'cms-about-body.html',
            ],
            'faq' => [
                'title' => 'FAQ',
                'meta_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'file' => 'cms-faq-body.html',
            ],
            'contact' => [
                'title' => 'Contact Us',
                'meta_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'file' => 'cms-contact-intro-body.html',
            ],
            'inventory' => [
                'title' => 'Inventory',
                'meta_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'file' => '',
            ],
            'compare' => [
                'title' => 'Compare Vehicles',
                'meta_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'file' => '',
            ],
            'listing-detail' => [
                'title' => 'Vehicle Detail',
                'meta_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'file' => '',
            ],
        ];

        foreach ($definitions as $slug => $def) {
            $path = database_path('seed-data/' . $def['file']);
            $html = is_file($path) ? (string) file_get_contents($path) : '';

            CmsPage::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $def['title'],
                    'meta_description' => $def['meta_description'],
                    'content_html' => $html,
                    'is_active' => true,
                ]
            );
        }
    }
}

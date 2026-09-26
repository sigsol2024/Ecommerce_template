<?php

namespace Database\Seeders;

use App\Models\PageSection;
use Illuminate\Database\Seeder;

class PageSectionsSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            'home' => [
                ['section_key' => 'hero_title', 'content_type' => 'text', 'content' => 'Lorem ipsum dolor sit amet'],
                ['section_key' => 'hero_subtitle', 'content_type' => 'text', 'content' => 'Consectetur adipiscing elit, sed do eiusmod tempor incididunt'],
                ['section_key' => 'hero_cta_text', 'content_type' => 'text', 'content' => 'Lorem CTA'],
                ['section_key' => 'hero_cta_href', 'content_type' => 'text', 'content' => '/inventory'],
                ['section_key' => 'home_search_label', 'content_type' => 'text', 'content' => 'Lorem ipsum — search inventory'],
                ['section_key' => 'recent_title', 'content_type' => 'text', 'content' => 'Lorem dolor sit amet'],
                ['section_key' => 'recent_subtitle', 'content_type' => 'textarea', 'content' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Cards below are live listings.'],
                ['section_key' => 'hero_image', 'content_type' => 'image', 'content' => 'asset/images/media/home-hero-main.jpg'],
                ['section_key' => 'cta_left_image', 'content_type' => 'image', 'content' => 'asset/images/media/home-cta-left.jpg'],
                ['section_key' => 'cta_right_image', 'content_type' => 'image', 'content' => 'asset/images/media/home-cta-right.jpg'],
                ['section_key' => 'cta_left_title', 'content_type' => 'text', 'content' => 'Lorem ipsum dolor'],
                ['section_key' => 'cta_left_body', 'content_type' => 'textarea', 'content' => 'Sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'],
                ['section_key' => 'cta_right_title', 'content_type' => 'text', 'content' => 'Consectetur adipiscing'],
                ['section_key' => 'cta_right_body', 'content_type' => 'textarea', 'content' => 'Elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.'],
                ['section_key' => 'feat1_title', 'content_type' => 'text', 'content' => 'Lorem ipsum'],
                ['section_key' => 'feat1_body', 'content_type' => 'textarea', 'content' => 'Dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero.'],
                ['section_key' => 'feat2_title', 'content_type' => 'text', 'content' => 'Dolor sit amet'],
                ['section_key' => 'feat2_body', 'content_type' => 'textarea', 'content' => 'Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet.'],
                ['section_key' => 'feat3_title', 'content_type' => 'text', 'content' => 'Consectetur elit'],
                ['section_key' => 'feat3_body', 'content_type' => 'textarea', 'content' => 'Fusce nec tellus sed augue semper porta. Mauris massa. Vestibulum lacinia arcu eget nulla.'],
                ['section_key' => 'welcome_title', 'content_type' => 'text', 'content' => 'Lorem ipsum welcome block'],
                ['section_key' => 'welcome_body', 'content_type' => 'textarea', 'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta.'],
                ['section_key' => 'welcome_video_url', 'content_type' => 'text', 'content' => ''],
                ['section_key' => 'prefooter_title', 'content_type' => 'text', 'content' => 'Lorem ipsum — questions?'],
                ['section_key' => 'prefooter_button_text', 'content_type' => 'text', 'content' => 'Contact'],
                ['section_key' => 'prefooter_button_href', 'content_type' => 'text', 'content' => '/contact'],
                ['section_key' => 'testimonial_name', 'content_type' => 'text', 'content' => 'Lorem Ipsum'],
                ['section_key' => 'testimonial_role', 'content_type' => 'text', 'content' => 'Lorem role'],
                ['section_key' => 'testimonial_quote', 'content_type' => 'textarea', 'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In fringilla, velit id laoreet hendrerit, sapien nisl varius dolor, eu consequat erat augue in eros.'],
            ],
            'inventory' => [
                ['section_key' => 'heading', 'content_type' => 'text', 'content' => 'Vehicles For Sale'],
                ['section_key' => 'intro', 'content_type' => 'textarea', 'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Inventory cards are dynamic and loaded from vehicle records.'],
                ['section_key' => 'fallback_image', 'content_type' => 'image', 'content' => 'asset/images/media/inventory-listing-fallback.jpg'],
            ],
            'contact' => [
                ['section_key' => 'heading', 'content_type' => 'text', 'content' => 'Contact Us'],
                ['section_key' => 'intro', 'content_type' => 'textarea', 'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
                ['section_key' => 'hero_image', 'content_type' => 'image', 'content' => 'asset/images/media/contact-hero-bg.jpg'],
                ['section_key' => 'map_image', 'content_type' => 'image', 'content' => 'asset/images/media/contact-map.jpg'],
            ],
            'compare' => [
                ['section_key' => 'heading', 'content_type' => 'text', 'content' => 'Compare Vehicles'],
                ['section_key' => 'intro', 'content_type' => 'textarea', 'content' => 'Lorem ipsum dolor sit amet. Compare list remains dynamic from selected inventory records.'],
            ],
            'listing-detail' => [
                ['section_key' => 'heading', 'content_type' => 'text', 'content' => 'Vehicle Detail'],
                ['section_key' => 'intro', 'content_type' => 'textarea', 'content' => 'Lorem ipsum dolor sit amet. Gallery and specifications are loaded from listing data.'],
            ],
        ];

        foreach ($sections as $page => $items) {
            foreach ($items as $item) {
                PageSection::query()->updateOrCreate(
                    [
                        'page' => $page,
                        'section_key' => $item['section_key'],
                    ],
                    [
                        'content_type' => $item['content_type'],
                        'content' => $item['content'],
                    ]
                );
            }
        }
    }
}

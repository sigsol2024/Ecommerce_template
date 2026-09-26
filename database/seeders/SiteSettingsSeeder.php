<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'dealer_phone' => '+1 212-226-3126',
            'dealer_address' => '1840 E Garvey Ave South West Covina, CA 91791',
            'dealer_hours_label' => 'Work Hours',
            'dealer_sales_phone' => '(888) 354-1781',
            'dealer_sales_hours' => "Mon – Fri: 09:00AM – 09:00PM\nSaturday: 09:00AM – 07:00PM\nSunday: Closed",
            'footer_about' => 'Fusce interdum ipsum egestas urna amet fringilla, et placerat ex venenatis. Aliquet luctus pharetra. Proin sed fringilla lectus… ar sit amet tellus in mollis. Proin nec egestas nibh, eget egestas urna. Phasellus sit amet vehicula nunc. In hac habitasse platea dictumst.',
            'copyright_line' => 'MyAutoTorque',
            'social_facebook' => 'https://www.facebook.com/',
            'social_instagram' => 'https://www.instagram.com/',
            'social_linkedin' => 'https://www.linkedin.com/',
            'social_youtube' => 'https://www.youtube.com/',
        ];

        foreach ($defaults as $key => $value) {
            SiteSetting::query()->firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        \Illuminate\Support\Facades\Cache::forget('site_settings_array');
    }
}

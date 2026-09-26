<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = (int) $this->faker->numberBetween(2005, (int) date('Y'));
        $title = $year.' Demo Vehicle '.$this->faker->unique()->numerify('###');
        $slug = Str::slug($title);

        return [
            'user_id' => 1,
            'title' => $title,
            'slug' => $slug,
            'status' => 'draft',
            'year' => $year,
            'make_listing_option_id' => null,
            'model_listing_option_id' => null,
            'condition_listing_option_id' => null,
            'body_type_listing_option_id' => null,
            'transmission_listing_option_id' => null,
            'fuel_type_listing_option_id' => null,
            'drive_listing_option_id' => null,
            'country_listing_option_id' => null,
            'price' => $this->faker->numberBetween(5000, 250000),
            'mileage' => $this->faker->numberBetween(0, 180000),
            'engine_size' => $this->faker->randomElement(['2.0L', '3.5L V6', 'Electric']),
            'street_address' => null,
            'features' => $this->faker->randomElements(
                ['Leather seats', 'Sunroof', 'Navigation', 'Backup camera', 'Heated seats'],
                $this->faker->numberBetween(1, 3)
            ),
            'exterior_color' => $this->faker->safeColorName(),
            'interior_color' => $this->faker->safeColorName(),
            'vin' => strtoupper(Str::random(17)),
            'description' => $this->faker->paragraphs(3, true),
        ];
    }
}

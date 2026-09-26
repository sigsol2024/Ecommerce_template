<?php

/**
 * Map raw vehicle field strings (normalized key = mb_strtolower(trim)) to canonical labels per category slug.
 * Used by `php artisan listing-options:migrate-legacy` before inserting listing_options.
 */
return [
    'make' => [
        'bmw' => 'BMW',
        'mercedes' => 'Mercedes-Benz',
        'mercedes-benz' => 'Mercedes-Benz',
        'vw' => 'Volkswagen',
        'chevy' => 'Chevrolet',
    ],
    'model' => [
        'm4 competition' => 'M4',
    ],
    'body_type' => [
        'suv' => 'SUV',
        'sedan' => 'Sedan',
        'coupe' => 'Coupe',
        'wagon' => 'Wagon',
        'roadster' => 'Roadster',
    ],
    'country' => [
        'us' => 'United States',
        'usa' => 'United States',
        'u.s.' => 'United States',
        'u.s.a.' => 'United States',
        'united states of america' => 'United States',
        'uk' => 'United Kingdom',
        'uae' => 'United Arab Emirates',
        'ng' => 'Nigeria',
    ],
    'drive' => [
        '4wd' => '4WD',
        'four wheel drive' => '4WD',
        'four-wheel drive' => '4WD',
        'awd' => 'AWD',
        'all wheel drive' => 'AWD',
        'all-wheel drive' => 'AWD',
        '2wd' => '2WD',
        'two wheel drive' => '2WD',
        'fwd' => 'FWD',
        'front wheel drive' => 'FWD',
        'rwd' => 'RWD',
        'rear wheel drive' => 'RWD',
    ],
    'transmission' => [
        'auto' => 'Automatic',
        'automatic transmission' => 'Automatic',
        'cvt' => 'CVT',
        'manual transmission' => 'Manual',
        'stick' => 'Manual',
    ],
    'fuel_type' => [
        'gas' => 'Petrol',
        'gasoline' => 'Petrol',
        'unleaded' => 'Petrol',
        'diesel fuel' => 'Diesel',
    ],
    'condition' => [
        'pre-owned' => 'used',
        'second hand' => 'used',
        'second-hand' => 'used',
    ],
];

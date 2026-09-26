<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'public_email',
        'public_phone',
        'public_address',
        'map_location',
        'show_on_listings',
        'whatsapp',
        'website',
    ];

    protected function casts(): array
    {
        return [
            'show_on_listings' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

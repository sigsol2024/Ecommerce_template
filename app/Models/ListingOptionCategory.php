<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListingOptionCategory extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'slug',
        'label',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(ListingOption::class, 'category_id')->orderBy('sort_order')->orderBy('value');
    }

    public function activeRootOptions(): HasMany
    {
        return $this->options()->whereNull('parent_id')->where('is_active', true);
    }
}

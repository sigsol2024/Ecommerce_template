<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_settings_array'));
        static::deleted(fn () => Cache::forget('site_settings_array'));
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $row = static::query()->where('key', $key)->first();

        return $row?->value ?? $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        Cache::forget('site_settings_array');
    }

    /** @return array<string, string|null> */
    public static function allKeyed(): array
    {
        return Cache::rememberForever('site_settings_array', function () {
            return static::query()->pluck('value', 'key')->all();
        });
    }
}

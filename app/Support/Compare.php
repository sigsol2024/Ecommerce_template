<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

final class Compare
{
    private const KEY = 'compare.vehicle_ids';

    /**
     * @return array<int, int>
     */
    public static function ids(): array
    {
        /** @var array<int, int|string> $raw */
        $raw = Session::get(self::KEY, []);
        $ids = [];
        foreach ($raw as $id) {
            $ids[] = (int) $id;
        }
        $ids = array_values(array_unique(array_filter($ids)));
        return $ids;
    }

    public static function count(): int
    {
        return count(self::ids());
    }

    public static function add(int $vehicleId): void
    {
        $ids = self::ids();
        $ids[] = $vehicleId;
        Session::put(self::KEY, array_values(array_unique($ids)));
    }

    public static function remove(int $vehicleId): void
    {
        $ids = array_values(array_filter(self::ids(), fn ($id) => $id !== $vehicleId));
        Session::put(self::KEY, $ids);
    }

    public static function clear(): void
    {
        Session::forget(self::KEY);
    }
}


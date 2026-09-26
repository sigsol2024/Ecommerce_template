<?php

namespace App\Http\Controllers;

use App\Models\ListingOption;
use App\Models\ListingOptionCategory;
use Illuminate\Http\JsonResponse;

class ListingOptionLookupController extends Controller
{
    public function modelsForMake(ListingOption $make): JsonResponse
    {
        $makeCategory = ListingOptionCategory::query()->where('slug', 'make')->firstOrFail();
        if ((int) $make->category_id !== (int) $makeCategory->id || $make->parent_id !== null) {
            abort(404);
        }

        $modelCategory = ListingOptionCategory::query()->where('slug', 'model')->firstOrFail();
        $rows = ListingOption::query()
            ->where('category_id', $modelCategory->id)
            ->where('parent_id', $make->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('value')
            ->get(['id', 'value']);

        return response()->json(['models' => $rows]);
    }
}

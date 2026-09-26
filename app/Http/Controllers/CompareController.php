<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Support\Compare;
use Illuminate\Http\RedirectResponse;
class CompareController extends Controller
{
    public function add(Vehicle $vehicle): RedirectResponse
    {
        if ($vehicle->status !== 'approved') {
            abort(404);
        }

        Compare::add($vehicle->id);

        return back();
    }

    public function remove(Vehicle $vehicle): RedirectResponse
    {
        Compare::remove($vehicle->id);

        return back();
    }

    public function clear(): RedirectResponse
    {
        Compare::clear();

        return back();
    }
}


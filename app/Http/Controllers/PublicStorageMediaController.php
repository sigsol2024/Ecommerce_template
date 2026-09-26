<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicStorageMediaController extends Controller
{
    public function show(string $path): Response|BinaryFileResponse
    {
        $clean = ltrim($path, '/');
        if ($clean === '' || ! Storage::disk('public')->exists($clean)) {
            abort(404);
        }

        $fullPath = Storage::disk('public')->path($clean);

        return response()->file($fullPath);
    }
}


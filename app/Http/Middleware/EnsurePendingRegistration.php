<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePendingRegistration
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('pending_registration')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}

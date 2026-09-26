<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLoginOtpPending
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('login_otp_user_id')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}

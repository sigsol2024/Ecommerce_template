<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnifiedAuthController extends Controller
{
    public function show(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        if ($request->session()->has('login_otp_user_id')) {
            return redirect()->route('login.otp.show');
        }

        if ($request->session()->has('pending_registration')) {
            return redirect()->route('register.verify.show');
        }

        return view('auth.unified');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request (step 1: send OTP, do not create user yet).
     *
     * @throws ValidationException
     */
    public function store(Request $request, EmailOtpService $otp): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $token = Str::random(48);
        $request->session()->put('pending_registration', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $request->session()->put('pending_registration_otp_token', $token);

        try {
            if (! $otp->issueForRegistration($token, $data['email'], $data['name'])) {
                $request->session()->forget(['pending_registration', 'pending_registration_otp_token']);

                return back()
                    ->withInput()
                    ->withErrors(['email' => __('Please wait before requesting another code.')]);
            }
        } catch (\Throwable $e) {
            report($e);
            $request->session()->forget(['pending_registration', 'pending_registration_otp_token']);

            return back()
                ->withInput()
                ->withErrors(['email' => __('Could not send verification email. Check mail configuration and try again.')]);
        }

        return redirect()->route('register.verify.show');
    }
}

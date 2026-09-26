<?php

namespace App\Http\Controllers;

use App\Models\VendorProfile;
use App\Services\Auth\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $profile = $user->vendorProfile ?? new VendorProfile(['user_id' => $user->id, 'show_on_listings' => true]);

        return view('dashboard.vendor-settings', [
            'title' => __('Seller profile'),
            'profile' => $profile,
        ]);
    }

    public function update(Request $request, EmailOtpService $otp): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'business_name' => ['nullable', 'string', 'max:255'],
            'public_email' => ['nullable', 'email', 'max:255'],
            'public_phone' => ['nullable', 'string', 'max:64'],
            'public_address' => ['nullable', 'string', 'max:500'],
            'map_location' => ['nullable', 'string', 'max:500'],
            'show_on_listings' => ['nullable', 'boolean'],
            'website' => ['nullable', 'string', 'max:500'],
            'whatsapp' => ['nullable', 'string', 'max:64'],
            'otp_code' => ['nullable', 'string', 'size:6'],
        ]);

        VendorProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => $validated['business_name'] ?? null,
                'public_email' => $validated['public_email'] ?? null,
                'public_phone' => $validated['public_phone'] ?? null,
                'public_address' => $validated['public_address'] ?? null,
                'map_location' => $validated['map_location'] ?? null,
                'show_on_listings' => $request->boolean('show_on_listings'),
                'website' => $validated['website'] ?? null,
                'whatsapp' => $validated['whatsapp'] ?? null,
            ]
        );

        $wantsOtp = $request->boolean('email_login_otp_enabled');
        $hadOtp = $user->email_login_otp_enabled;
        $code = $request->string('otp_code')->toString();

        if (! $wantsOtp) {
            $request->session()->forget('otp_settings_pending');
            if ($hadOtp) {
                $user->forceFill(['email_login_otp_enabled' => false])->save();
            }

            return redirect()
                ->route('dashboard.vendor-settings.edit')
                ->with('status', __('Seller profile saved.'));
        }

        // wants OTP on
        if ($hadOtp) {
            return redirect()
                ->route('dashboard.vendor-settings.edit')
                ->with('status', __('Seller profile saved.'));
        }

        if ($code !== '') {
            if (! $otp->verifyOtpSettingsEnable((int) $user->id, $code)) {
                return back()->withErrors(['otp_code' => __('Invalid or expired code.')]);
            }
            $request->session()->forget('otp_settings_pending');
            $user->forceFill(['email_login_otp_enabled' => true])->save();

            return redirect()
                ->route('dashboard.vendor-settings.edit')
                ->with('status', __('Login email codes are now enabled.'));
        }

        if ($request->session()->get('otp_settings_pending') !== true) {
            try {
                if (! $otp->issueForOtpSettingsEnable($user)) {
                    return back()->withErrors(['email_login_otp_enabled' => __('Wait before requesting another code.')]);
                }
            } catch (\Throwable $e) {
                report($e);

                return back()->withErrors(['email_login_otp_enabled' => __('Could not send verification email.')]);
            }
            $request->session()->put('otp_settings_pending', true);

            return redirect()
                ->route('dashboard.vendor-settings.edit')
                ->with('status', __('We emailed you a code. Enter it in “Confirmation code” and save again to enable login OTP.'));
        }

        return back()->withErrors(['otp_code' => __('Enter the code from your email to enable login OTP.')]);
    }
}

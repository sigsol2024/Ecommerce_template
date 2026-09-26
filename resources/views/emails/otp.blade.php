@extends('emails.layouts.branded')

@section('content')
  <p style="margin:0 0 16px;">{{ __('Hello,') }}</p>
  <p style="margin:0 0 20px;">
    @if ($purpose === \App\Services\Auth\EmailOtpService::PURPOSE_REGISTRATION)
      {{ __('Use this code to finish creating your account:') }}
    @elseif ($purpose === \App\Services\Auth\EmailOtpService::PURPOSE_LOGIN)
      {{ __('Use this code to complete sign-in:') }}
    @elseif ($purpose === \App\Services\Auth\EmailOtpService::PURPOSE_OTP_SETTINGS_ENABLE)
      {{ __('Use this code to confirm enabling email login codes on your account:') }}
    @else
      {{ __('Your verification code:') }}
    @endif
  </p>
  <div style="margin:24px 0;text-align:center;">
    <span style="display:inline-block;font-size:32px;font-weight:800;letter-spacing:0.25em;color:#4e77ed;font-family:ui-monospace,Menlo,monospace;">{{ $code }}</span>
  </div>
  <p style="margin:0 0 8px;font-size:13px;color:#52525b;">
    {{ __('This code expires in :minutes minutes.', ['minutes' => $expiresMinutes]) }}
  </p>
  <p style="margin:16px 0 0;font-size:12px;color:#a1a1aa;">
    {{ __('If you did not request this, you can ignore this email.') }}
  </p>
@endsection

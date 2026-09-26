@extends('emails.layouts.branded')

@section('content')
  <p style="margin:0 0 16px;">{{ __('Hello :name,', ['name' => $user->name]) }}</p>
  <p style="margin:0 0 20px;">{{ __('You requested a password reset for your account. Click the button below to choose a new password.') }}</p>
  <div style="margin:28px 0;text-align:center;">
    <a href="{{ $resetUrl }}" style="display:inline-block;padding:14px 28px;background:#4e77ed;color:#ffffff;text-decoration:none;font-weight:700;border-radius:8px;font-size:15px;">{{ __('Reset password') }}</a>
  </div>
  <p style="margin:0;font-size:12px;color:#71717a;word-break:break-all;">{{ __('If the button does not work, copy this link into your browser:') }}<br><a href="{{ $resetUrl }}" style="color:#4e77ed;">{{ $resetUrl }}</a></p>
  <p style="margin:20px 0 0;font-size:12px;color:#a1a1aa;">{{ __('This link will expire as configured by your site administrator. If you did not request a reset, ignore this email.') }}</p>
@endsection

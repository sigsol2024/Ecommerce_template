<x-guest-layout>
  <x-auth-session-status class="mb-4" :status="session('status')" />

  <h2 class="text-lg font-semibold text-gray-900">{{ __('Check your email') }}</h2>
  <p class="mt-2 text-sm text-gray-600">{{ __('Enter the 6-digit code to finish signing in.') }}</p>

  <form method="POST" action="{{ route('login.otp.store') }}" class="mt-6 space-y-4">
    @csrf
    <div>
      <x-input-label for="code" :value="__('Code')" />
      <x-text-input id="code" class="block mt-1 w-full tracking-widest text-center text-xl font-mono" type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" required autofocus autocomplete="one-time-code" />
      <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>
    <x-primary-button class="w-full justify-center">{{ __('Verify') }}</x-primary-button>
  </form>

  <form method="POST" action="{{ route('login.otp.resend') }}" class="mt-4">
    @csrf
    <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800 underline">{{ __('Resend code') }}</button>
  </form>

  <form method="POST" action="{{ route('login.otp.cancel') }}" class="mt-6 text-center">
    @csrf
    <button type="submit" class="text-sm text-gray-500 underline hover:text-gray-800">{{ __('Cancel and return to sign in') }}</button>
  </form>
</x-guest-layout>

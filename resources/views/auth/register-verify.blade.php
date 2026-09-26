<x-guest-layout>
  <x-auth-session-status class="mb-4" :status="session('status')" />

  <h2 class="text-lg font-semibold text-gray-900">{{ __('Verify your email') }}</h2>
  <p class="mt-2 text-sm text-gray-600">{{ __('Enter the 6-digit code we sent to your inbox.') }}</p>

  <form method="POST" action="{{ route('register.verify.store') }}" class="mt-6 space-y-4">
    @csrf
    <div>
      <x-input-label for="code" :value="__('Code')" />
      <x-text-input id="code" class="block mt-1 w-full tracking-widest text-center text-xl font-mono" type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" required autofocus autocomplete="one-time-code" />
      <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>
    <x-primary-button class="w-full justify-center">{{ __('Verify & create account') }}</x-primary-button>
  </form>

  <form method="POST" action="{{ route('register.verify.resend') }}" class="mt-4">
    @csrf
    <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800 underline">{{ __('Resend code') }}</button>
  </form>
</x-guest-layout>

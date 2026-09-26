<x-guest-layout>
  <div class="mb-4 rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-100">
    <strong>Temporary page.</strong> Creates the <strong>first</strong> admin (Spatie role <code class="rounded bg-black/10 px-1">admin</code>).
    After use: set <code class="rounded bg-black/10 px-1">ADMIN_BOOTSTRAP_ENABLED=false</code> in <code class="rounded bg-black/10 px-1">.env</code> and remove the bootstrap routes from <code class="rounded bg-black/10 px-1">routes/web.php</code>.
  </div>

  @if($blocked ?? false)
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
      <p class="font-semibold">An admin already exists — this page is blocked.</p>
      @if(($admins ?? collect())->isNotEmpty())
        <ul class="mt-2 list-disc pl-5">
          @foreach($admins as $a)
            <li>{{ $a->email }} @if($a->name) ({{ $a->name }}) @endif</li>
          @endforeach
        </ul>
      @endif
      <p class="mt-3 text-xs opacity-90">
        To create a new first admin, remove the admin role from existing users in the database, or delete this user and clear related
        <code class="rounded bg-black/10 px-1">model_has_roles</code> rows, then refresh this page.
      </p>
    </div>
  @else
    <form method="POST" action="{{ route('bootstrap.admin.store') }}">
      @csrf

      <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
      </div>

      <div class="mt-4">
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
      </div>

      <div class="mt-4" x-data="{ showPassword: false }">
        <x-input-label for="password" :value="__('Password')" />
        <div class="relative mt-1">
          <x-text-input id="password" class="block w-full pr-10" x-bind:type="showPassword ? 'text' : 'password'" type="password" name="password" required autocomplete="new-password" />
          <button type="button" class="absolute inset-y-0 right-0 inline-flex items-center px-3 text-gray-500 hover:text-gray-700" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Hide password' : 'Show password'">
            <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/><path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587a2.25 2.25 0 003.182 3.182"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.878 5.099A9.77 9.77 0 0112 4.875c4.478 0 8.268 2.943 9.543 7.006a9.743 9.743 0 01-4.181 5.312M6.228 6.228A9.744 9.744 0 002.457 11.88a9.77 9.77 0 005.57 6.228"/></svg>
          </button>
        </div>
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
      </div>

      <div class="mt-4" x-data="{ showPasswordConfirmation: false }">
        <x-input-label for="password_confirmation" :value="__('Confirm password')" />
        <div class="relative mt-1">
          <x-text-input id="password_confirmation" class="block w-full pr-10" x-bind:type="showPasswordConfirmation ? 'text' : 'password'" type="password" name="password_confirmation" required autocomplete="new-password" />
          <button type="button" class="absolute inset-y-0 right-0 inline-flex items-center px-3 text-gray-500 hover:text-gray-700" @click="showPasswordConfirmation = !showPasswordConfirmation" :aria-label="showPasswordConfirmation ? 'Hide password confirmation' : 'Show password confirmation'">
            <svg x-show="!showPasswordConfirmation" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <svg x-show="showPasswordConfirmation" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/><path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587a2.25 2.25 0 003.182 3.182"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.878 5.099A9.77 9.77 0 0112 4.875c4.478 0 8.268 2.943 9.543 7.006a9.743 9.743 0 01-4.181 5.312M6.228 6.228A9.744 9.744 0 002.457 11.88a9.77 9.77 0 005.57 6.228"/></svg>
          </button>
        </div>
      </div>

      <div class="mt-6 flex items-center justify-end">
        <x-primary-button>{{ __('Create admin') }}</x-primary-button>
      </div>
    </form>
  @endif
</x-guest-layout>

<x-app-layout>
  @php
    $pageCount = count($pages);
  @endphp

  <x-admin.page-header
    :title="__('Page editors')"
    :subtitle="__('Edit public storefront pages, SEO, and section copy.')"
    :count="trans_choice(':count page|:count pages', $pageCount, ['count' => number_format($pageCount)])"
  />

  <x-admin.page-content>
    @if (session('status'))
      <div class="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        {{ session('status') }}
      </div>
    @endif

    <div class="bg-white border border-wp-border rounded overflow-hidden hidden md:block">
      <table class="w-full text-left border-collapse text-sm">
        <thead class="bg-wp-bg border-b border-wp-border">
          <tr>
            <th class="px-4 py-3 font-semibold text-wp-text-muted text-xs uppercase tracking-wide">{{ __('Page') }}</th>
            <th class="px-4 py-3 font-semibold text-wp-text-muted text-xs uppercase tracking-wide">{{ __('Summary') }}</th>
            <th class="px-4 py-3 font-semibold text-wp-text-muted text-xs uppercase tracking-wide w-28">{{ __('Content') }}</th>
            <th class="px-4 py-3 font-semibold text-wp-text-muted text-xs uppercase tracking-wide w-28">{{ __('Status') }}</th>
            <th class="px-4 py-3 font-semibold text-wp-text-muted text-xs uppercase tracking-wide text-right w-36">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($pages as $slug => $info)
            @php
              $entry = $existing[$slug] ?? null;
              $isSaved = $entry !== null;
              $isActive = $entry?->is_active ?? true;
            @endphp
            <tr class="border-b border-wp-border last:border-0 hover:bg-wp-bg/60">
              <td class="px-4 py-3 align-top">
                <div class="font-medium text-wp-text">{{ $info['label'] }}</div>
                <div class="mt-0.5 font-mono text-[11px] text-wp-text-muted">/{{ $slug === 'inventory' ? 'shop' : $slug }}</div>
              </td>
              <td class="px-4 py-3 align-top text-wp-text-muted text-sm max-w-md">
                {{ $info['default_description'] }}
              </td>
              <td class="px-4 py-3 align-top">
                <x-admin.status-pill :variant="$isSaved ? 'success' : 'neutral'">
                  {{ $isSaved ? __('Saved') : __('Default') }}
                </x-admin.status-pill>
              </td>
              <td class="px-4 py-3 align-top">
                <x-admin.status-pill :variant="$isActive ? 'success' : 'warning'">
                  {{ $isActive ? __('Active') : __('Inactive') }}
                </x-admin.status-pill>
              </td>
              <td class="px-4 py-3 align-top text-right">
                <x-admin.button variant="secondary" :href="route('admin.pages.edit', ['slug' => $slug])">
                  {{ __('Edit') }}
                </x-admin.button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="grid grid-cols-1 gap-3 md:hidden">
      @foreach ($pages as $slug => $info)
        @php
          $entry = $existing[$slug] ?? null;
          $isSaved = $entry !== null;
          $isActive = $entry?->is_active ?? true;
        @endphp
        <a
          href="{{ route('admin.pages.edit', ['slug' => $slug]) }}"
          class="block rounded border border-wp-border bg-white p-4 hover:border-wp-link transition-colors"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <h3 class="font-medium text-wp-text">{{ $info['label'] }}</h3>
              <p class="mt-0.5 font-mono text-[11px] text-wp-text-muted">/{{ $slug === 'inventory' ? 'shop' : $slug }}</p>
            </div>
            <div class="flex flex-col items-end gap-1 shrink-0">
              <x-admin.status-pill :variant="$isSaved ? 'success' : 'neutral'">
                {{ $isSaved ? __('Saved') : __('Default') }}
              </x-admin.status-pill>
              <x-admin.status-pill :variant="$isActive ? 'success' : 'warning'">
                {{ $isActive ? __('Active') : __('Inactive') }}
              </x-admin.status-pill>
            </div>
          </div>
          <p class="mt-2 text-sm text-wp-text-muted line-clamp-2">{{ $info['default_description'] }}</p>
          <p class="mt-3 text-sm font-medium text-wp-link">{{ __('Open editor') }} →</p>
        </a>
      @endforeach
    </div>
  </x-admin.page-content>

  @include('admin.partials.luxe-footer', ['footerClass' => 'mt-8'])
</x-app-layout>

@php
  $imgClass = $imgClass ?? 'h-full w-full object-cover';
  $fallbackUrl = $fallback
    ?? \App\Support\PlaceholderMedia::url('asset/images/media/inventory-listing-fallback.jpg');
  $urls = $vehicle->images
    ->take(12)
    ->map(fn ($im) => \App\Support\VehicleImageUrl::url($im->path))
    ->values()
    ->all();
  if ($urls === []) {
    $urls = [$fallbackUrl];
  }
  $multi = count($urls) > 1;
@endphp
<div
  class="relative h-full w-full overflow-hidden {{ $multi ? 'cursor-crosshair touch-manipulation' : '' }}"
  data-vehicle-hover-gallery
  data-images='@json($urls)'
>
  <img
    src="{{ $urls[0] }}"
    alt="{{ $vehicle->title }}"
    class="{{ $imgClass }} {{ $multi ? 'pointer-events-none select-none' : '' }}"
    data-vehicle-hover-main
    loading="lazy"
    decoding="async"
  />
  @if ($multi)
    <div
      class="pointer-events-none absolute inset-x-0 bottom-0 flex justify-center gap-1 pb-2 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
      aria-hidden="true"
    >
      @foreach ($urls as $i => $_u)
        <span
          class="h-1 w-5 max-w-[14%] shrink-0 rounded-full bg-white/40 transition-[transform,background-color] data-[active=1]:scale-110 data-[active=1]:bg-white"
          data-vehicle-hover-dot
          data-active="{{ $i === 0 ? '1' : '0' }}"
        ></span>
      @endforeach
    </div>
  @endif
</div>

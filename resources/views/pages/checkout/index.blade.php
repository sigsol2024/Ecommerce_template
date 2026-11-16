@extends('layouts.site')

@section('content')
  @php
    $hasPaystack = collect($paymentMethods)->contains(fn ($m) => $m['id'] === 'paystack');
    $hasBank = collect($paymentMethods)->contains(fn ($m) => $m['id'] === 'bank_transfer');
    $hasCod = collect($paymentMethods)->contains(fn ($m) => $m['id'] === 'pay_on_delivery');
  @endphp
  <div class="luxe-store bg-background text-on-background min-h-screen luxe-geometric-bg font-body-md">
    <div class="max-w-max-container mx-auto px-margin-mobile md:px-gutter pt-24 md:pt-28 pb-section-py-mobile md:pb-section-py-desktop">
      <nav class="mb-8 md:mb-10 flex items-center gap-2 font-label-caps text-label-caps text-on-surface-variant">
        <a href="{{ route('shop.index') }}" class="hover:text-black">{{ __('Shop') }}</a>
        <span>/</span>
        <a href="{{ route('cart.index') }}" class="hover:text-black">{{ __('Bag') }}</a>
        <span>/</span>
        <span class="text-black">{{ __('Checkout') }}</span>
      </nav>

      <h1 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg uppercase mb-8 md:mb-10">{{ __('Checkout') }}</h1>

      @if (session('status'))
        <div class="mb-8 rounded border border-outline-variant bg-white px-4 py-3 text-sm text-on-surface">
          {{ session('status') }}
        </div>
      @endif

      @if (empty($paymentMethods))
        <div class="mb-8 rounded border border-[#ba1a1a]/30 bg-[#ffdad6]/40 px-4 py-3 text-sm text-[#93000a]">
          {{ __('No payment methods are enabled yet. Please contact the store or try again later.') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="mb-8 rounded border border-[#ba1a1a]/30 bg-[#ffdad6]/40 px-4 py-3 text-sm text-[#93000a]">
          <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="post" action="{{ route('checkout.store') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12" id="checkout-form" data-checkout-page>
        @csrf

        {{-- Contact / shipping / payment: second on mobile, left on desktop --}}
        <div class="order-2 lg:order-1 lg:col-span-7 space-y-10 bg-surface-container-low p-6 md:p-8 rounded-sm border border-outline-variant">
          <section>
            <h2 class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#4c4546] mb-6">{{ __('Contact') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <input name="customer_name" value="{{ old('customer_name') }}" required class="w-full bg-white/80 border-0 border-b border-[#cfc4c5] py-3 px-2 focus:ring-0 focus:border-black" placeholder="{{ __('Full name') }}" />
              </div>
              <div>
                <input name="customer_email" type="email" value="{{ old('customer_email') }}" required class="w-full bg-white/80 border-0 border-b border-[#cfc4c5] py-3 px-2 focus:ring-0 focus:border-black" placeholder="{{ __('Email') }}" />
              </div>
              <div>
                <input name="customer_phone" value="{{ old('customer_phone') }}" class="w-full bg-white/80 border-0 border-b border-[#cfc4c5] py-3 px-2 focus:ring-0 focus:border-black" placeholder="{{ __('Phone') }}" />
              </div>
            </div>
          </section>

          <section>
            <h2 class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#4c4546] mb-6">{{ __('Shipping address') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <input name="shipping_address_line1" value="{{ old('shipping_address_line1') }}" required class="w-full bg-white/80 border-0 border-b border-[#cfc4c5] py-3 px-2 focus:ring-0 focus:border-black" placeholder="{{ __('Street address') }}" />
              </div>
              <div class="md:col-span-2">
                <input name="shipping_address_line2" value="{{ old('shipping_address_line2') }}" class="w-full bg-white/80 border-0 border-b border-[#cfc4c5] py-3 px-2 focus:ring-0 focus:border-black" placeholder="{{ __('Apartment, suite, etc. (optional)') }}" />
              </div>
              <div>
                <input name="shipping_city" value="{{ old('shipping_city') }}" required class="w-full bg-white/80 border-0 border-b border-[#cfc4c5] py-3 px-2 focus:ring-0 focus:border-black" placeholder="{{ __('City') }}" />
              </div>
              <div>
                <input name="shipping_state" value="{{ old('shipping_state') }}" class="w-full bg-white/80 border-0 border-b border-[#cfc4c5] py-3 px-2 focus:ring-0 focus:border-black" placeholder="{{ __('State') }}" />
              </div>
              <div>
                <input name="shipping_postal_code" value="{{ old('shipping_postal_code') }}" class="w-full bg-white/80 border-0 border-b border-[#cfc4c5] py-3 px-2 focus:ring-0 focus:border-black" placeholder="{{ __('Postal code') }}" />
              </div>
              <div>
                <select name="shipping_country" required class="w-full bg-white/80 border-0 border-b border-[#cfc4c5] py-3 px-2 focus:ring-0 focus:border-black">
                  <option value="NG" @selected(old('shipping_country', 'NG') === 'NG')>{{ __('Nigeria') }}</option>
                  <option value="GH" @selected(old('shipping_country') === 'GH')>{{ __('Ghana') }}</option>
                  <option value="GB" @selected(old('shipping_country') === 'GB')>{{ __('United Kingdom') }}</option>
                  <option value="US" @selected(old('shipping_country') === 'US')>{{ __('United States') }}</option>
                </select>
              </div>
            </div>
          </section>

          <section>
            <h2 class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#4c4546] mb-4">{{ __('Payment method') }}</h2>
            <div class="flex flex-col sm:flex-row flex-wrap gap-2" role="radiogroup" aria-label="{{ __('Payment method') }}" data-payment-method-group>
              @if ($hasPaystack)
                <label class="payment-method-card inline-flex flex-row items-center gap-2 px-3 py-2 border rounded-sm cursor-pointer transition-all bg-white/90 w-full sm:w-auto sm:max-w-[11.5rem]" data-payment-method-card>
                  <input type="radio" name="payment_method" value="paystack" class="sr-only" @checked(old('payment_method', $defaultPaymentMethod) === 'paystack') required />
                  <img src="{{ asset('asset/images/brands/paystack-logo.png') }}" alt="Paystack" class="h-5 w-auto max-w-[96px] object-contain shrink-0" width="96" height="18" />
                  <span class="text-[9px] uppercase tracking-wide text-on-surface-variant leading-tight">{{ __('Card & online') }}</span>
                </label>
              @endif
              @if ($hasBank)
                <label class="payment-method-card inline-flex flex-row items-center gap-2 px-3 py-2 border rounded-sm cursor-pointer transition-all bg-white/90 w-full sm:w-auto sm:max-w-[11.5rem]" data-payment-method-card>
                  <input type="radio" name="payment_method" value="bank_transfer" class="sr-only" @checked(old('payment_method', $defaultPaymentMethod) === 'bank_transfer') @if(! $hasPaystack) required @endif />
                  <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-surface-container text-[#3A3C94]">
                    <x-icon name="building-library" class="w-4 h-4" />
                  </span>
                  <span class="text-[10px] font-semibold uppercase tracking-wide text-primary leading-tight">{{ __('Bank transfer') }}</span>
                </label>
              @endif
              @if ($hasCod)
                <label class="payment-method-card inline-flex flex-row items-center gap-2 px-3 py-2 border rounded-sm cursor-pointer transition-all bg-white/90 w-full sm:w-auto sm:max-w-[11.5rem]" data-payment-method-card>
                  <input type="radio" name="payment_method" value="pay_on_delivery" class="sr-only" @checked(old('payment_method', $defaultPaymentMethod) === 'pay_on_delivery') @if(! $hasPaystack && ! $hasBank) required @endif />
                  <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-surface-container text-[#3A3C94]">
                    <x-icon name="truck" class="w-4 h-4" />
                  </span>
                  <span class="text-[10px] font-semibold uppercase tracking-wide text-primary leading-tight">{{ __('Pay on delivery') }}</span>
                </label>
              @endif
            </div>
          </section>

          {{-- Mobile: Place order after the form --}}
          <div class="lg:hidden space-y-3 pt-2 border-t border-outline-variant">
            <button type="submit" class="w-full bg-black text-white py-5 text-xs font-bold uppercase tracking-[0.25em] hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed" data-checkout-submit @disabled(empty($paymentMethods))>
              {{ __('Place order') }}
            </button>
            <a href="{{ route('cart.index') }}" class="block w-full text-center border border-outline-variant text-on-surface-variant py-3 text-xs font-bold uppercase tracking-[0.2em] hover:border-primary hover:text-primary transition-colors">
              {{ __('Back to bag') }}
            </a>
          </div>
        </div>

        {{-- Order summary: first on mobile, right on desktop --}}
        <aside class="order-1 lg:order-2 lg:col-span-5">
          <div class="lg:sticky lg:top-32 bg-white p-6 md:p-8 border border-[#cfc4c5] space-y-6">
            <h2 class="text-xl uppercase border-b border-[#cfc4c5] pb-4">{{ __('Order summary') }}</h2>
            <div class="space-y-4 max-h-80 overflow-y-auto" data-checkout-lines>
              @foreach ($lines as $line)
                <div
                  class="flex gap-3 sm:gap-4 text-sm"
                  data-checkout-line
                  data-vehicle-id="{{ (int) $line['vehicle_id'] }}"
                  @if (! empty($line['vehicle_variant_id'])) data-variant-id="{{ (int) $line['vehicle_variant_id'] }}" @endif
                >
                  @if (!empty($line['image']))
                    <img src="{{ $line['image'] }}" alt="" class="w-14 h-[4.5rem] sm:w-16 sm:h-20 object-cover bg-[#eee] shrink-0" />
                  @endif
                  <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                      <p class="font-bold uppercase text-xs truncate">{{ $line['name'] }}</p>
                      <button
                        type="button"
                        class="shrink-0 inline-flex items-center justify-center p-1 text-[#4c4546] hover:text-[#ba1a1a] transition-colors disabled:opacity-40"
                        data-checkout-remove
                        aria-label="{{ __('Remove') }} {{ $line['name'] }}"
                        title="{{ __('Remove') }}"
                      >
                        <x-icon name="close" class="w-4 h-4" />
                      </button>
                    </div>
                    @if (!empty($line['variant_label']))
                      <p class="text-[10px] uppercase tracking-wide text-[#4c4546] mt-1">{{ $line['variant_label'] }}</p>
                    @endif
                    <p class="text-[#4c4546] text-xs mt-1">× {{ $line['qty'] }}</p>
                    <p class="font-medium mt-1" data-checkout-line-total>{{ format_currency($line['unit_price'] * $line['qty']) }}</p>
                  </div>
                </div>
              @endforeach
            </div>
            <div class="border-t border-[#cfc4c5] pt-4 flex justify-between font-bold">
              <span>{{ __('Total') }}</span>
              <span data-checkout-total>{{ format_currency($subtotal) }}</span>
            </div>

            {{-- Desktop: actions stay in the summary column --}}
            <div class="hidden lg:block space-y-3">
              <a href="{{ route('cart.index') }}" class="block w-full text-center border border-outline-variant text-on-surface-variant py-3 text-xs font-bold uppercase tracking-[0.2em] hover:border-primary hover:text-primary transition-colors">
                {{ __('Back to bag') }}
              </a>
              <button type="submit" class="w-full bg-black text-white py-5 text-xs font-bold uppercase tracking-[0.25em] hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed" data-checkout-submit @disabled(empty($paymentMethods))>
                {{ __('Place order') }}
              </button>
            </div>
          </div>
        </aside>
      </form>
    </div>
  </div>
  @push('scripts')
  <script>
    (function () {
      var form = document.getElementById('checkout-form');
      if (!form) return;

      var removeUrl = @json(route('cart.remove'));
      var cartUrl = @json(route('cart.index'));
      var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
      var btns = form.querySelectorAll('[data-checkout-submit]');

      var selectedColor = '#3A3C94';
      var selectedBg = 'rgba(58, 60, 148, 0.08)';

      function syncPaymentCards() {
        if (!btns.length) return;
        var selected = form.querySelector('input[name="payment_method"]:checked');
        var val = selected ? selected.value : '';
        form.querySelectorAll('[data-payment-method-card]').forEach(function (card) {
          var input = card.querySelector('input[type="radio"]');
          var on = input && input.checked;
          card.style.borderColor = on ? selectedColor : '#cfc4c5';
          card.style.backgroundColor = on ? selectedBg : 'rgba(255, 255, 255, 0.9)';
          card.style.boxShadow = on ? '0 0 0 1px ' + selectedColor : 'none';
        });
        var label = val === 'paystack'
          ? @json(__('Pay with Paystack'))
          : @json(__('Place order'));
        btns.forEach(function (btn) { btn.textContent = label; });
      }

      form.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
        radio.addEventListener('change', syncPaymentCards);
      });
      form.querySelectorAll('[data-payment-method-card]').forEach(function (card) {
        card.addEventListener('click', function () {
          var input = card.querySelector('input[type="radio"]');
          if (input) {
            input.checked = true;
            syncPaymentCards();
          }
        });
      });
      syncPaymentCards();

      function syncHeaderBadge(count) {
        document.querySelectorAll('[data-cart-count-badge]').forEach(function (el) {
          el.textContent = count > 9 ? '9+' : String(count);
          el.classList.toggle('hidden', !(count > 0));
        });
      }

      form.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-checkout-remove]');
        if (!btn) return;
        e.preventDefault();

        var row = btn.closest('[data-checkout-line]');
        if (!row || btn.disabled) return;

        var payload = { vehicle_id: row.getAttribute('data-vehicle-id') };
        var variantId = row.getAttribute('data-variant-id');
        if (variantId) payload.vehicle_variant_id = variantId;

        btn.disabled = true;
        var fd = new FormData();
        Object.keys(payload).forEach(function (k) { fd.append(k, payload[k]); });

        fetch(removeUrl, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf,
          },
          body: fd,
          credentials: 'same-origin',
        })
          .then(function (res) {
            if (!res.ok) {
              return res.json().catch(function () { return {}; }).then(function () {
                throw new Error(@json(__('Could not remove item.')));
              });
            }
            return res.json();
          })
          .then(function (state) {
            row.remove();
            syncHeaderBadge(state.count || 0);

            var totalEl = form.querySelector('[data-checkout-total]');
            if (totalEl && state.subtotal_formatted) {
              totalEl.textContent = state.subtotal_formatted;
            }

            if (!state.count || state.count <= 0) {
              window.location.href = cartUrl;
              return;
            }
          })
          .catch(function () {
            btn.disabled = false;
          });
      });
    })();
  </script>
  @endpush
@endsection

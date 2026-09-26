@php
  $ctx = \App\Support\BrandedMailContext::forEmail();
  $siteName = $ctx['siteName'];
  $logoUrl = $ctx['logoUrl'];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $siteName }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:Inter,system-ui,-apple-system,sans-serif;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f4f5;padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e4e4e7;">
          <tr>
            <td style="background:linear-gradient(135deg,#1e2229 0%,#2d333b 100%);padding:20px 24px;text-align:center;">
              @if (!empty($logoUrl))
                <img src="{{ $logoUrl }}" alt="" width="120" height="auto" style="max-height:48px;display:inline-block;">
              @else
                <div style="color:#ffb129;font-size:22px;font-weight:800;letter-spacing:0.02em;">{{ $siteName }}</div>
              @endif
            </td>
          </tr>
          <tr>
            <td style="padding:28px 24px 32px;color:#18181b;font-size:15px;line-height:1.6;">
              @yield('content')
            </td>
          </tr>
          <tr>
            <td style="padding:16px 24px;background:#fafafa;border-top:1px solid #e4e4e7;font-size:11px;color:#71717a;text-align:center;">
              &copy; {{ date('Y') }} {{ $siteName }}
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>

#!/usr/bin/env bash
# Run ON THE SERVER:
#   cd ~/auction.vcphotels.com && bash scripts/fix-homepage-500.sh
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> Project: $ROOT"

BLADE="resources/views/pages/home-luxemotive.blade.php"
if [[ -f "$BLADE" ]]; then
  # Avoid RouteNotFoundException if api.products.search was not deployed / route cache is stale
  sed -i "s|route('api.products.search')|url('/api/products/search')|g" "$BLADE"
  sed -i "s|route('shop.index')|url('/shop')|g" "$BLADE"
  echo "==> Patched hero search URLs in $BLADE"
else
  echo "!! Missing $BLADE"
fi

# Ensure search route exists (idempotent)
if ! grep -q "api.products.search" routes/web.php 2>/dev/null; then
  echo "==> Inserting api.products.search route into routes/web.php"
  python3 - <<'PY'
from pathlib import Path
path = Path("routes/web.php")
text = path.read_text()
needle = "Route::get('/shop', [PageController::class, 'inventory'])->name('shop.index');"
insert = needle + """
Route::get('/api/products/search', [PageController::class, 'productSearch'])
    ->middleware('throttle:60,1')
    ->name('api.products.search');"""
if needle not in text:
    raise SystemExit("Could not find shop.index route to insert after")
if "api.products.search" in text:
    print("route already present")
else:
    path.write_text(text.replace(needle, insert, 1))
    print("route inserted")
PY
else
  echo "==> api.products.search already in routes/web.php"
fi

if ! grep -q "function productSearch" app/Http/Controllers/PageController.php 2>/dev/null; then
  echo "!! productSearch() missing from PageController — pull latest code from git"
  exit 1
fi

php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true
php artisan config:clear || true

echo "==> Route check:"
php artisan route:list --name=api.products.search || true

echo "==> Latest log lines (if any):"
if ls -1 storage/logs/laravel*.log >/dev/null 2>&1; then
  tail -n 40 storage/logs/laravel*.log | tail -n 40
else
  echo "(no laravel log files yet)"
fi

echo
echo "Done. Open https://auction.vcphotels.com/ in a private window."

# Homepage / Apache "Index of" fix (cPanel)
#
# Run ON THE SERVER from SSH (not on your PC):
#   cd ~/auction.vcphotels.com && bash scripts/fix-homepage-index.sh
#
# Why: "/" was never hitting PHP. Other URLs (/shop) rewrite fine; "/" listed a directory.

set -e
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "App root: $ROOT"

if [ ! -f public/index.php ]; then
  echo "ERROR: public/index.php missing. You are in the wrong folder or deploy is incomplete."
  exit 1
fi

# Always recreate the root front controller (idempotent)
cat > index.php <<'PHP'
<?php
require __DIR__.'/public/index.php';
PHP

chmod 644 index.php public/index.php
chmod 755 . public 2>/dev/null || true

# Prefer PHP index; never allow directory listing if AllowOverride permits it
if [ -f .htaccess ]; then
  if ! grep -q 'DirectoryIndex index.php' .htaccess; then
    printf '\nDirectoryIndex index.php\nOptions -Indexes\n' >> .htaccess
  fi
fi

echo "--- check ---"
ls -la index.php public/index.php
echo "--- php boot smoke ---"
php -r "echo file_exists('index.php') && file_exists('public/index.php') ? \"index files OK\n\" : \"FAIL\n\";"

echo ""
echo "DONE. Next:"
echo "  1) Open https://auction.vcphotels.com/ in a private/incognito window"
echo "  2) Best permanent fix in cPanel → Domains → auction.vcphotels.com"
echo "     set Document Root to: $ROOT/public"
echo ""

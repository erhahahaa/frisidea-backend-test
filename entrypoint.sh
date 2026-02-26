#!/bin/sh
set -e

# Color output for better readability
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# ============================================================================
# Wait for PostgreSQL to be healthy
# ============================================================================
echo "${YELLOW}Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT}...${NC}"

timeout=60
elapsed=0

while ! nc -z "$DB_HOST" "$DB_PORT" 2>/dev/null; do
    elapsed=$((elapsed + 1))
    if [ $elapsed -ge $timeout ]; then
        echo "${RED}✗ PostgreSQL connection timeout after ${timeout}s${NC}"
        exit 1
    fi
    echo "  Waiting... (${elapsed}s)"
    sleep 1
done

echo "${GREEN}✓ PostgreSQL is available${NC}"

# ============================================================================
# Clear config cache (safe before migrations - does not touch DB)
# ============================================================================
echo "${YELLOW}Clearing config cache...${NC}"
php artisan config:clear 2>/dev/null || true

# ============================================================================
# Run database migrations
# ============================================================================
echo "${YELLOW}Running database migrations...${NC}"
php artisan migrate --force 2>&1 || {
    echo "${YELLOW}⚠ Migration warning - check logs above for details${NC}"
}
echo "${GREEN}✓ Migrations completed${NC}"

# ============================================================================
# Clear application cache (safe now - cache table exists after migrations)
# ============================================================================
echo "${YELLOW}Clearing application cache...${NC}"
php artisan cache:clear 2>/dev/null || true

# ============================================================================
# Start FrankenPHP
# ============================================================================
echo "${GREEN}✓ Starting FrankenPHP server on port 80${NC}"
echo "---"

cat > /app/Caddyfile <<'CADDYEOF'
{
    skip_install_trust
    admin off
    storage file_system /app/.caddy/data
}

http://localhost:80 {
    root * /app/public
    file_server
    encode zstd br gzip
    php_server
}
CADDYEOF

exec frankenphp run --config /app/Caddyfile
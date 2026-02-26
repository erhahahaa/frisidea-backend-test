#!/bin/sh
set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# ============================================================================
# Ensure required directories exist with proper permissions
# ============================================================================
mkdir -p /app/.caddy/data /app/.caddy/config

# ============================================================================
# Wait for PostgreSQL
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
# Laravel bootstrap
# ============================================================================
echo "${YELLOW}Clearing config cache...${NC}"
php artisan config:clear 2>/dev/null || true

echo "${YELLOW}Running database migrations...${NC}"
if ! php artisan migrate --force 2>&1; then
    echo "${RED}✗ Migrations failed — aborting${NC}"
    exit 1
fi
echo "${GREEN}✓ Migrations completed${NC}"

echo "${YELLOW}Clearing application cache...${NC}"
php artisan cache:clear 2>/dev/null || true

# ============================================================================
# Start FrankenPHP
# ============================================================================
echo "${GREEN}✓ Starting FrankenPHP on port 80${NC}"
echo "---"

exec frankenphp run --config /app/Caddyfile
# Multi-stage Dockerfile for Laravel with FrankenPHP
# Supports both development and production builds

# ============================================================================
# Stage 1: Frontend Builder (Node.js - Vite Assets Compilation)
# ============================================================================
FROM node:20-alpine AS frontend-builder

WORKDIR /app

# Copy package files
COPY package.json package-lock.json ./

# Install all dependencies (including dev for Vite build)
RUN npm ci

# Copy source files for Vite
COPY resources/ ./resources/
COPY vite.config.js ./

# Build frontend assets
RUN npm run build

# ============================================================================
# Stage 2: PHP Dependencies Builder (Composer)
# ============================================================================
FROM dunglas/frankenphp:latest AS composer-builder

WORKDIR /app

# Copy composer files and vendor (already installed locally)
COPY composer.json composer.lock ./
COPY vendor ./vendor

# Optimize autoloader for production
RUN php -r "require 'vendor/autoload.php';" && \
    find vendor -type f -name "*.php" -path "*/tests/*" -delete && \
    find vendor -type f -name "*.md" -delete

# ============================================================================
# Stage 3: Development Image
# ============================================================================
FROM dunglas/frankenphp:latest AS dev

WORKDIR /app

# Install required system packages
RUN apt-get update && apt-get install -y --no-install-recommends \
    # Essential tools
    curl \
    wget \
    git \
    unzip \
    netcat-traditional \
    # Timezone support
    tzdata \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions using the official FrankenPHP image installer
RUN install-php-extensions \
    pgsql \
    pdo_pgsql \
    pdo_sqlite \
    redis \
    memcached \
    opcache \
    intl \
    xml \
    curl \
    mbstring \
    bcmath \
    json

# Create non-root user for running FrankenPHP
RUN groupadd -g 1000 frankenphp && \
    useradd -m -u 1000 -g frankenphp -s /sbin/nologin frankenphp

# Copy composer vendor from builder stage
COPY --from=composer-builder --chown=frankenphp:frankenphp /app/vendor ./vendor

# Copy frontend build output
COPY --from=frontend-builder --chown=frankenphp:frankenphp /app/public/build ./public/build

# Copy the entire Laravel application
COPY --chown=frankenphp:frankenphp . .

# Set proper permissions for storage and bootstrap directories
RUN mkdir -p storage bootstrap/cache && \
    chown -R frankenphp:frankenphp storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Copy entrypoint script
COPY --chown=frankenphp:frankenphp entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Switch to non-root user
USER frankenphp

# Expose port 80
EXPOSE 80

# Set environment variables
ENV APP_ENV=local \
    APP_DEBUG=true \
    FRANKENPHP_CONFIG="worker"

# Entrypoint script handles app startup and migrations
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# ============================================================================
# Stage 4: Production Image (Optimized)
# ============================================================================
FROM dunglas/frankenphp:latest AS prod

WORKDIR /app

# Install only essential system packages (no dev tools)
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    netcat-traditional \
    tzdata \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (production-optimized)
RUN install-php-extensions \
    pgsql \
    pdo_pgsql \
    pdo_sqlite \
    redis \
    memcached \
    opcache \
    intl \
    xml \
    curl \
    mbstring \
    bcmath \
    json

# Create non-root user
RUN groupadd -g 1000 frankenphp && \
    useradd -m -u 1000 -g frankenphp -s /sbin/nologin frankenphp

# Copy composer vendor from builder (production dependencies only)
COPY --from=composer-builder --chown=frankenphp:frankenphp /app/vendor ./vendor

# Copy frontend build output
COPY --from=frontend-builder --chown=frankenphp:frankenphp /app/public/build ./public/build

# Copy the entire Laravel application
COPY --chown=frankenphp:frankenphp . .

# Set proper permissions for storage and bootstrap directories
RUN mkdir -p storage bootstrap/cache && \
    chown -R frankenphp:frankenphp storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Copy entrypoint script
COPY --chown=frankenphp:frankenphp entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Switch to non-root user
USER frankenphp

# Expose port 80
EXPOSE 80

# Set environment variables (production)
ENV APP_ENV=production \
    APP_DEBUG=false \
    FRANKENPHP_CONFIG="worker"

# Entrypoint script handles app startup
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

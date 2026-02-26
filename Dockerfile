# ============================================================================
# Stage 1: Composer
# ============================================================================
FROM composer:2 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

# ============================================================================
# Stage 2: Development
# ============================================================================
FROM dunglas/frankenphp:php8.4-alpine AS dev

WORKDIR /app

RUN apk add --no-cache \
    curl \
    netcat-openbsd \
    tzdata \
    git

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

RUN addgroup -g 1000 frankenphp && \
    adduser -D -u 1000 -G frankenphp -s /sbin/nologin frankenphp

COPY --from=composer-builder --chown=frankenphp:frankenphp /app/vendor ./vendor
COPY --chown=frankenphp:frankenphp . .
COPY --chown=root:root Caddyfile /app/Caddyfile

RUN mkdir -p /data/caddy/pki && \
    mkdir -p storage/framework/{sessions,views,cache} \
             storage/logs \
             bootstrap/cache && \
    chown -R frankenphp:frankenphp \
        /data/caddy \
        storage \
        bootstrap/cache && \
    chmod -R 775 /data/caddy storage bootstrap/cache

COPY --chown=frankenphp:frankenphp entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER frankenphp

EXPOSE 80

ENV APP_ENV=local \
    APP_DEBUG=true \
    FRANKENPHP_CONFIG="worker"

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# ============================================================================
# Stage 3: Production
# ============================================================================
FROM dunglas/frankenphp:php8.4-alpine AS prod

WORKDIR /app

RUN apk add --no-cache \
    curl \
    netcat-openbsd \
    tzdata

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

RUN addgroup -g 1000 frankenphp && \
    adduser -D -u 1000 -G frankenphp -s /sbin/nologin frankenphp

COPY --from=composer-builder --chown=frankenphp:frankenphp /app/vendor ./vendor
COPY --chown=frankenphp:frankenphp . .
COPY --chown=root:root Caddyfile /app/Caddyfile

RUN mkdir -p /data/caddy/pki && \
    mkdir -p storage/framework/{sessions,views,cache} \
             storage/logs \
             bootstrap/cache && \
    chown -R frankenphp:frankenphp \
        /data/caddy \
        storage \
        bootstrap/cache && \
    chmod -R 775 /data/caddy storage bootstrap/cache

COPY --chown=frankenphp:frankenphp entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER frankenphp

EXPOSE 80

ENV APP_ENV=production \
    APP_DEBUG=false \
    FRANKENPHP_CONFIG="worker"

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
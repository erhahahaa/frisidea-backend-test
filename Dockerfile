# ============================================================================
# Stage 1: PHP Dependencies Builder (Composer)
# ============================================================================
FROM dunglas/frankenphp:latest AS composer-builder

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

# ============================================================================
# Stage 2: Development Image
# ============================================================================
FROM dunglas/frankenphp:latest AS dev

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    wget \
    git \
    unzip \
    netcat-traditional \
    tzdata \
    && rm -rf /var/lib/apt/lists/*

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

RUN groupadd -g 1000 frankenphp && \
    useradd -m -u 1000 -g frankenphp -s /sbin/nologin frankenphp

COPY --from=composer-builder --chown=frankenphp:frankenphp /app/vendor ./vendor

COPY --chown=frankenphp:frankenphp . .

COPY --chown=root:root Caddyfile /app/Caddyfile

RUN mkdir -p storage/framework/{sessions,views,cache} \
             storage/logs \
             bootstrap/cache \
             /app/.caddy/data \
             /app/.caddy/config && \
    chown -R frankenphp:frankenphp \
        storage \
        bootstrap/cache \
        /app/.caddy && \
    chmod -R 775 storage bootstrap/cache

COPY --chown=frankenphp:frankenphp entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER frankenphp

EXPOSE 80

ENV APP_ENV=local \
    APP_DEBUG=true \
    FRANKENPHP_CONFIG="worker" \
    XDG_DATA_HOME=/app/.caddy/data \
    XDG_CONFIG_HOME=/app/.caddy/config

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# ============================================================================
# Stage 3: Production Image
# ============================================================================
FROM dunglas/frankenphp:latest AS prod

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    netcat-traditional \
    tzdata \
    && rm -rf /var/lib/apt/lists/*

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

RUN groupadd -g 1000 frankenphp && \
    useradd -m -u 1000 -g frankenphp -s /sbin/nologin frankenphp

COPY --from=composer-builder --chown=frankenphp:frankenphp /app/vendor ./vendor

COPY --chown=frankenphp:frankenphp . .

COPY --chown=root:root Caddyfile /app/Caddyfile

RUN mkdir -p storage/framework/{sessions,views,cache} \
             storage/logs \
             bootstrap/cache \
             /app/.caddy/data \
             /app/.caddy/config && \
    chown -R frankenphp:frankenphp \
        storage \
        bootstrap/cache \
        /app/.caddy && \
    chmod -R 775 storage bootstrap/cache

COPY --chown=frankenphp:frankenphp entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER frankenphp

EXPOSE 80

ENV APP_ENV=production \
    APP_DEBUG=false \
    FRANKENPHP_CONFIG="worker" \
    XDG_DATA_HOME=/app/.caddy/data \
    XDG_CONFIG_HOME=/app/.caddy/config

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
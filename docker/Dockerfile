ARG PHP_VERSION=8.4-fpm

FROM php:${PHP_VERSION} AS runtime_base
ENV TZ="Europe/Paris"
# Librairies partagées
RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends \
        libicu-dev libzip-dev libpng-dev libjpeg-dev libldap2-dev \
        libmemcached-dev libpq-dev libssl-dev libbz2-dev libfreetype6-dev \
        memcached curl netcat-openbsd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

FROM runtime_base AS builder
# Outils de compilation pas nécessaires à l'exécution
RUN apt-get update && apt-get install -y --no-install-recommends \ 
        $PHPIZE_DEPS \
        nodejs npm \
        zlib1g-dev g++ git libcurl4-gnutls-dev \
        libedit-dev libldb-dev libreadline-dev zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure ldap \
    && CURRENT_PHP_VERSION="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')" \
    && if dpkg --compare-versions "$CURRENT_PHP_VERSION" ge 8.5; then \
        EXT_LIST="bcmath bz2 gettext sockets pdo_mysql zip gd intl ldap"; \
    else \
        EXT_LIST="bcmath bz2 gettext opcache sockets pdo pdo_mysql zip gd intl ldap"; \
    fi \
    && docker-php-ext-install -j"$(nproc)" $EXT_LIST \
    && pecl install memcached apcu xdebug && docker-php-ext-enable memcached apcu xdebug \
    && echo "xdebug.mode=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci
COPY webpack.config.js ./
COPY assets/ assets/
COPY public/ public/
RUN npm run build

COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-scripts -vvv
COPY . .

# On base de l'image runtime pour ne pas embarquer les outils de compilation
FROM runtime_base AS symfony_runtime
WORKDIR /app
# On désactive Dotenv car on envoie tout dans le docker compose
ENV SYMFONY_DOTENV_VARS=false
RUN groupadd -g 1000 appgroup && \
    useradd -u 1000 -g appgroup -m -s /bin/sh appuser

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d
COPY docker/php/php.ini /usr/local/etc/php/conf.d/z-php.ini

#COPY --from=builder --chown=appuser:appgroup /app /app
#RUN rm -rf /app/public/build && mkdir -p /app/public/build
# On ne prend que le nécessaire
COPY --from=builder --chown=appuser:appgroup /app/bin ./bin
COPY --from=builder --chown=appuser:appgroup /app/public/build ./public/build
COPY --from=builder --chown=appuser:appgroup /app/assets/custom ./assets/custom
COPY --from=builder --chown=appuser:appgroup /app/assets/template/scss ./assets/template/scss
COPY --from=builder --chown=appuser:appgroup /app/config ./config
COPY --from=builder --chown=appuser:appgroup /app/migrations ./migrations
COPY --from=builder --chown=appuser:appgroup /app/public ./public
COPY --from=builder --chown=appuser:appgroup /app/src ./src
COPY --from=builder --chown=appuser:appgroup /app/templates ./templates
COPY --from=builder --chown=appuser:appgroup /app/translations ./translations
COPY --from=builder --chown=appuser:appgroup /app/vendor ./vendor
COPY --from=builder --chown=appuser:appgroup /app/composer.* ./
COPY --from=builder --chown=appuser:appgroup /app/symfony.lock ./
COPY --from=builder /app/public/build/manifest.json /app/public/build/manifest.json
COPY --from=builder /app/public/build/entrypoints.json /app/public/build/entrypoints.json

# TESTS
COPY --from=builder --chown=appuser:appgroup /app/tests ./tests
COPY --from=builder --chown=appuser:appgroup /app/phpunit.xml.dist ./phpunit.xml.dist
COPY --from=builder --chown=appuser:appgroup /app/grumphp.yml ./grumphp.yml
COPY --from=builder --chown=appuser:appgroup /app/.php-cs-fixer.dist.php ./.php-cs-fixer.dist.php
COPY --from=builder --chown=appuser:appgroup /app/phpcs.xml.dist ./phpcs.xml.dist
COPY --from=builder --chown=appuser:appgroup /app/phpinsights.php ./phpinsights.php
COPY --from=builder --chown=appuser:appgroup /app/infection.json.dist ./infection.json.dist
# Deptrac
COPY --from=builder --chown=appuser:appgroup /app/deptrac.yaml ./deptrac.yaml
# PHPStan
COPY --from=builder --chown=appuser:appgroup /app/phpstan.neon ./phpstan.neon

# Pour Dockle (Aucun Dockerfile dans une image docker)
RUN find /app/vendor -name "Dockerfile" -type f -delete
# On crée un fichier .env vide pour le Runtime de Symfony
# RUN touch .env > .env
RUN mkdir -p /app/var/cache /app/var/log && \
    chown -R appuser:appgroup /app/var && \
    chmod -R 775 /app/var
# Reports pour la CI
RUN mkdir -p /app/var/reports && chown -R appuser:appgroup /app/var/reports
USER appuser
HEALTHCHECK --interval=10s --timeout=3s \
  CMD nc -z localhost 9000 || exit 1
EXPOSE 9000

# Nginx et symfony auront les mêmes assets car le cache Docker est utilisé
FROM nginx:alpine AS nginx_runtime
# apk update pour mettre à jour les packets si nouvelles CVE
RUN apk update && apk upgrade && \
    touch /var/run/nginx.pid && \
    chown -R nginx:nginx /var/run/nginx.pid /var/cache/nginx /var/log/nginx
COPY --from=builder /app/public /app/public
ARG NGINX_CONF=default-test.conf
# Si prod, on supprime ce qui ne sert à rien pour supprimer les surfaces d'attaque et le poids de l'image.
ARG NGINX_CONF=default-test.conf
RUN if [ "$NGINX_CONF" = "default-prod.conf" ] ; then \
        echo "--- HARDENING: Removing package manager ---" && \
        rm -rf /var/cache/apk/* && \
        rm -rf /lib/apk/db/* && \
        rm -f /sbin/apk ; \
    fi

COPY docker/nginx/${NGINX_CONF} /etc/nginx/conf.d/default.conf
RUN chown nginx:nginx /etc/nginx/conf.d/default.conf
USER nginx
HEALTHCHECK --interval=10s --timeout=3s \
  CMD curl -f http://localhost || exit 1
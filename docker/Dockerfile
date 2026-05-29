ARG PHP_VERSION=8.4-debian13-fpm@sha256:68bb3ce7d9ce1b8111f339c1f21994d9722426c7938fdd65c041fe3956613c2a

# Image de base pour la compilation des extensions PHP
FROM php:8.4-fpm AS runtime_base
ENV TZ="Europe/Paris"
# Librairies partagées
RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends \
        libicu-dev libzip-dev libpng-dev libjpeg-dev libldap2-dev \
        libmemcached-dev libpq-dev libssl-dev libbz2-dev libfreetype6-dev \
        memcached netcat-openbsd \
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
    && pecl install memcached apcu xdebug && docker-php-ext-enable memcached apcu \
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

# Img Hardened
FROM dhi.io/php:${PHP_VERSION} AS symfony_runtime
ARG CACHE_BUSTER=0
# On l'affiche ou on l'utilise pour casser le cache à ce niveau précis
#RUN echo "Semaine de build actuelle : ${CACHE_BUSTER}"
WORKDIR /app
# On désactive Dotenv car on envoie tout dans le docker compose
ENV SYMFONY_DOTENV_VARS=false
#RUN groupadd -g 1000 appgroup && \
#    useradd -u 1000 -g appgroup -m -s /bin/sh appuser

COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d
# PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/z-php.ini
# PHP-FPM
# COPY docker/php/zz-performance.conf /usr/local/etc/php-fpm.d/www.conf
# APP
# On ne prend que le nécessaire
COPY --from=builder --chown=appuser:appgroup /app/bin ./bin
#COPY --from=builder --chown=appuser:appgroup /app/public/build ./public/build
COPY --from=builder --chown=appuser:appgroup /app/assets/custom ./assets/custom
COPY --from=builder --chown=appuser:appgroup /app/assets/template/scss ./assets/template/scss
COPY --from=builder --chown=appuser:appgroup /app/config ./config
COPY --from=builder --chown=appuser:appgroup /app/migrations ./migrations
COPY --from=builder --chown=appuser:appgroup /app/public ./public
# le build c'est pour nginx
#RUN rm -rf /app/public/build 
COPY --from=builder --chown=appuser:appgroup /app/public/build/manifest.json /app/public/build/manifest.json
COPY --from=builder --chown=appuser:appgroup /app/public/build/entrypoints.json /app/public/build/entrypoints.json
COPY --from=builder --chown=appuser:appgroup /app/src ./src
COPY --from=builder --chown=appuser:appgroup /app/templates ./templates
COPY --from=builder --chown=appuser:appgroup /app/translations ./translations
COPY --from=builder --chown=appuser:appgroup /app/vendor ./vendor
COPY --from=builder --chown=appuser:appgroup /app/composer.* ./
COPY --from=builder --chown=appuser:appgroup /app/symfony.lock ./

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
#RUN find /app/vendor -name "Dockerfile" -type f -delete
# On crée un fichier .env vide pour le Runtime de Symfony
# RUN touch .env > .env
#RUN mkdir -p /app/var/cache /app/var/log && \
#    chown -R appuser:appgroup /app/var && \
#    chmod -R 775 /app/var
# Reports pour la CI
#RUN mkdir -p /app/var/reports && chown -R appuser:appgroup /app/var/reports
#USER appuser
HEALTHCHECK --interval=10s --timeout=3s \
  CMD php-fpm -t || exit 1
EXPOSE 9000

# Nginx et symfony auront les mêmes assets car le cache Docker est utilisé
FROM cgr.dev/chainguard/nginx:latest-dev@sha256:f1cc5dcc70a806d689123e450446362bcd16a2c4da71164139c1ce562672376c AS nginx_runtime
ARG CACHE_BUSTER=0
# On l'affiche ou on l'utilise pour casser le cache à ce niveau précis
#RUN echo "Semaine de build actuelle : ${CACHE_BUSTER}"
# Img Hardened plus besoin de ça
# apk update pour mettre à jour les packets si nouvelles CVE
#RUN apk update && apk upgrade --no-cache && \
#RUN touch /var/run/nginx.pid && \
#RUN    chown -R nginx:nginx /var/run/nginx.pid /var/cache/nginx /var/log/nginx
COPY --from=builder /app/public /app/public
# Si prod, on supprime ce qui ne sert à rien pour supprimer les surfaces d'attaque et le poids de l'image.
ARG NGINX_CONF=default-test.conf
#RUN if [ "$NGINX_CONF" = "default-prod.conf" ] ; then \
#        echo "--- HARDENING: Removing package manager ---" && \
#        rm -rf /var/cache/apk/* && \
#        rm -rf /lib/apk/db/* && \
#        rm -f /sbin/apk ; \
#    fi
# Le chemin est différent de l'image officielle de Nginx /!\
#COPY docker/nginx/${NGINX_CONF} /etc/nginx/conf.d/nginx.conf
COPY docker/nginx/${NGINX_CONF} /etc/nginx/nginx.conf
#RUN chown nginx:nginx /etc/nginx/conf.d/default.conf
USER nginx
HEALTHCHECK CMD wget -qO- http://localhost:8080 || exit 1
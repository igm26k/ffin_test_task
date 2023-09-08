FROM php:8.1-rc-fpm

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    libssl-dev zlib1g-dev curl git unzip libxml2-dev libzip-dev libmemcached-dev librabbitmq-dev && \
    pecl install apcu memcached amqp && \
    docker-php-ext-install -j$(nproc) zip opcache intl soap && \
    docker-php-ext-enable apcu opcache memcached amqp && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY --from=composer /usr/bin/composer /usr/bin/composer

# Php.ini
ADD ./_docker/api/php.ini /usr/local/etc/php

# Entrypoint
ADD ./_docker/api/bin/docker-entrypoint.sh /usr/local/bin/
RUN chmod 777 /usr/local/bin/docker-entrypoint.sh \
    && ln -s /usr/local/bin/docker-entrypoint.sh /

# APP
ADD ./ /var/www/app

ENTRYPOINT ["docker-entrypoint.sh"]

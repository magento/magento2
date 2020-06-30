ARG PHP_VERSION="7.3.17"
FROM php:${PHP_VERSION:+${PHP_VERSION}-}fpm-alpine
LABEL maintainer="Cristian Pini"
ENV TZ=Europe/London
RUN apk update; \
apk upgrade; \
apk add --no-cache tzdata freetype freetype-dev libxslt-dev libpng libjpeg libxml2-dev zlib libwebp-dev libpng-dev jpeg-dev icu-dev zlib-dev libzip-dev; \
apk add --no-cache $PHPIZE_DEPS; \
docker-php-ext-configure gd --with-png-dir=/usr/include/ \
                                --with-jpeg-dir=/usr/include/ \
                                --with-webp-dir=/usr/include/ \
                                --with-freetype-dir=/usr/inlcude/ \
&& docker-php-ext-configure zip --with-libzip \
&& docker-php-ext-install mysqli bcmath gd pdo_mysql soap xsl zip intl opcache \
&& echo "memory_limit=2G" > /usr/local/etc/php/conf.d/memory-limit.ini \
&& echo "max_input_vars=1000" > /usr/local/etc/php/conf.d/max-input-vars-php.ini \
&& echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
&& curl -sS https://getcomposer.org/installer | php \
&& mv composer.phar /usr/local/bin/composer \
&& rm -rf /var/cache/* /tmp/* /var/tmp/*
## copy the fpm config over
COPY k8s-config/php-docker/fpm.www.conf /usr/local/etc/php-fpm.d/www.conf
COPY --chown=www-data . /var/www/html
USER www-data
RUN cd /var/www/html \
&& cp .htaccess pub/ \
&& mv pub pub.release \
&& composer install --no-cache --no-dev
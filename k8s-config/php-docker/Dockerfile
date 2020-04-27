ARG PHP_VERSION="7.2.1"
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
#&& echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini \
&& echo "max_input_vars=10000" > /usr/local/etc/php/conf.d/max-input-vars-php.ini \ 
&& mkdir -p /var/www/magento \
&& chown www-data.www-data /var/www/magento \
&& curl -sS https://getcomposer.org/installer | php \
&& mv composer.phar /usr/local/bin/composer \
&& rm -rf /var/cache/* /tmp/* /var/tmp/* 
COPY --chown=www-data . /var/www/magento
USER www-data
RUN cd /var/www/magento \
&& mv /var/www/magento/start.sh /tmp \
&& rm -rf /var/www/magento/.git \
&& composer install --no-cache \
&& composer config repositories.magento composer https://repo.magento.com/ \
&& chmod +x pub/get.php pub/static.php \
&& chmod u+x bin/magento /tmp/start.sh

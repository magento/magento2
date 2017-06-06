FROM php:7.0-apache
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libxml2-dev \
        libssl-dev \
        libgd-dev \
        zip \
        unzip \
        libxslt-dev \
        libicu-dev
RUN docker-php-ext-install pdo pdo_mysql mbstring dom mcrypt zip xsl intl
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && \
    docker-php-ext-install -j$(nproc) gd
COPY docker/php.ini /usr/local/etc/php/
RUN export PATH=$PATH:/var/www/html/bin
COPY . /var/www/html
VOLUME ["/var/www/html"]
WORKDIR /var/www/html
RUN chmod -R 777 .
RUN a2enmod rewrite
# Installing composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Running composer install to install magento2 dependencies
RUN composer install
WORKDIR /var/www/html/bin
ENV MAGENTO_LANGUAGE=en_US
ENV MAGENTO_CURRENCY=USD
ENV MAGENTO_TIMEZONE=Africa/Cairo
ENV MAGENTO_ADMIN_FIRSTNAME=magento
ENV MAGENTO_ADMIN_LASTNAME=magento
ENV MAGENTO_EMAIL=admin@magento.com
ENV MAGENTO_USER=admin
ENV MAGENTO_PASSWORD=admin123
ENV MAGENTO_DB_HOST=db:3306
ENV MAGENTO_DB_NAME=magento
ENV MAGENTO_DB_USER=magento
ENV MAGENTO_DB_PASSWORD=magento12345
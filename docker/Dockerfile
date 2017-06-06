FROM php:5.6-apache
##
# WORKDIR is /var/www/html
#
# Magento 2 depends on gd, which depends on libpng12-dev and libjpeg-dev
#                      intl, which depends on g++ and libicu-dev
#                      mcrypt, which depends on libmcrypt-dev
#                      pdo_mysql
#                      xsl, which depends on libxslt1-dev
#
# Composer is insufferable without zip.
#
# I install xdebug, but don't enable it by default.
#
# Docker's filesystem has some strange hangups with deletion.
# I get around this by being stubborn.
#

RUN echo 'Installing Magento Dependencies' \
 && a2enmod rewrite > /dev/null \
 && DEBIAN_NONINTERACTIVE=1 apt-get -qqy update \
 && DEBIAN_NONINTERACTIVE=1 apt-get -qqy install g++ \
                                                 git \
                                                 libicu-dev \
                                                 libjpeg-dev \
                                                 libmcrypt-dev \
                                                 libpng12-dev \
                                                 libxslt1-dev \
                                                 mysql-client \
 && docker-php-ext-configure gd --with-jpeg-dir=/usr/lib/x86_64-linux-gnu/ \
 && docker-php-ext-install gd \
                           intl \
                           mbstring \
                           mcrypt \
                           opcache \
                           pdo_mysql \
                           xsl \
                           zip \
 && pecl install -o -f xdebug \
 && until rm -rf /tmp/pear /var/lib/apt/lists; do :; done
RUN echo 'Installing Composer' \
 && curl -# http://getcomposer.org/composer.phar > /usr/local/bin/composer \
 && chmod +x /usr/local/bin/composer

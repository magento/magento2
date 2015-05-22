FROM php:5.6-apache
##
# WORKDIR is /var/www/html
#
# Magento 2 depends on gd, which depends on libpng12-dev
#                      mcrypt, which depends on libmcrypt-dev
#                      intl, which depends on g++ and libicu-dev
#
# Composer is insufferable without zip.
#
# Docker's filesystem has some strange hangups with deletion.
# I get around this by being stubborn.
#

RUN echo 'Installing Magento Dependencies' \
 && DEBIAN_NONINTERACTIVE=1 apt-get -qqy update \
 && DEBIAN_NONINTERACTIVE=1 apt-get -qqy install g++ \
                                                 git \
                                                 libicu-dev \
                                                 libmcrypt-dev \
                                                 libpng12-dev \
 && docker-php-ext-install gd \
                           intl \
                           mcrypt \
                           zip \
 && until rm -rf /var/lib/apt/lists; do :; done
COPY . /var/www/html
RUN echo 'Installing Composer' \
 && curl -# http://getcomposer.org/composer.phar > /usr/local/bin/composer \
 && chmod +x /usr/local/bin/composer
RUN echo 'Installing Magento 2' \
 && composer config repositories.firegento composer http://packages.firegento.com \
 && composer config repositories.magento composer http://packages.magento.com \
 && composer update \
 && echo 'Cleaning up filesystem permissions' \
 && chown -R www-data:www-data . \
 && find . \( -type d -exec chmod 0700 {} + \) -o \( -exec chmod 0600 {} + \)


#!/bin/sh

##########################################
##
## Release Actions
##
##########################################

## move files into shared folder so apache and fpm have access
find /var/www/magento -type f -or -type d -exec mv -f '{}' /var/www/html/ \;

## reset sample data
## clear cache etc
cd /var/www/html/magento
php bin/magento sampledata:reset
php bin/magento cache:flush
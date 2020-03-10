#!/bin/sh

#############################################
#
# Release Actions !!!
#
#############################################

find /var/www/magento -type f -or -type d -exec mv -f '{}' /var/www/html/ \;

cd /var/www/html/magento
php -d memory_limit=2G bin/magento sampledata:deploy
php bin/magento setup:upgrade
#php bin/magento setup:static-content:deploy -f
rm -rf var/cache/* var/page_cache/*

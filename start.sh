#!/bin/sh

find /var/www/magento -type f -or -type d -exec mv -f '{}' /var/www/html/ \;

cd /var/www/html/magento
php bin/magento sampledata:deploy
php bin/magento cache:flush

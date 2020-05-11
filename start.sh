#!/bin/sh

#############################################
#
# Release Actions !!!
#
#############################################

#find /var/www/magento -type f -or -type d -exec mv -f '{}' /var/www/html/ \;
#
cd /var/www/html
cp -a pub.new/* pub/
cp -a .htaccess pub/

#php bin/magento sampledata:deploy
php bin/magento setup:upgrade

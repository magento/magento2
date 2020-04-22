#!/bin/sh

#############################################
#
# Release Actions !!!
#
#############################################

#find /var/www/magento -type f -or -type d -exec mv -f '{}' /var/www/html/ \;
#
#cd /var/www/html/magento
#php -d memory_limit=2G bin/magento sampledata:deploy
#php bin/magento setup:upgrade

echo "<h1>HELLO WORLD!!!</h1>" > /var/www/html/magento/index.html


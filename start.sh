#!/bin/sh

#############################################
#
# Release Actions !!!
#
#############################################

find /var/www/magento -type f -or -type d -exec mv -f '{}' /var/www/html/ \;
#
cd /var/www/html/magento
php bin/magento sampledata:deploy
php bin/magento setup:upgrade

chmod 0775 /var/www/magento/media /var/www/magento/var
chgrp 82 /var/www/magento/media /var/www/magento/var
#mkdir /var/www/html/magento
#echo "<h1>HELLO WORLD!!!</h1>" > /var/www/html/magento/index.html


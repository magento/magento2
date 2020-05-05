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

#php bin/magento sampledata:deploy
#php bin/magento setup:upgrade

#mkdir /var/www/html/magento
#echo "<h1>HELLO WORLD!!!</h1>" > /var/www/html/magento/index.html


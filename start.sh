#!/bin/sh

#############################################
#
# Release Actions !!!
#
#############################################

#/// Copy the public data from the .new onto the peristent volume 
cd /var/www/html
cp -a pub.new/* pub/
cp -a .htaccess pub/

#/// run setup, deploy the static content and flush the cache :)
php bin/magento setup:static-content:deploy -f
php bin/magento setup:upgrade
php bin/magento deploy:mode:set production
php bin/magento cache:flush



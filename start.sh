#!/bin/sh

#############################################
#
# Release Actions !!!
#
#############################################

#/// Copy the public data from the .new onto the peristent volume 
cd /var/www/html

MODE=$(php bin/magento deploy:mode:show | sed -e 's/\.//g' | awk '{print $4}')
if [ $MODE != 'production' ]
then
    cp -a pub.new/* pub/
    cp -a .htaccess pub/
    #/// deploy the static content, compile di and flush the cache :)
    php bin/magento deploy:mode:set production
fi
php bin/magento cache:clean
echo "Startup complete ...."




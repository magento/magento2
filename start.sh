#!/bin/sh

#############################################
#
# Post Release Actions !!!
#
#############################################

#/// Copy the public data from the .new onto the peristent volume 
cd /var/www/html

MODE=$(php bin/magento deploy:mode:show | sed -e 's/\.//g' | awk '{print $4}')
if [ $MODE != 'production' ]
then
    #/// deploy the static content, compile di and flush the cache :)
    php bin/magento deploy:mode:set production
    php bin/magento maintenance:enable
    mv pub pub.new
fi
#echo "Release complete ...."




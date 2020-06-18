#!/bin/sh

#############################################
#
# Post Release Actions !!!
#
#############################################

#/// Copy the public data from the .new onto the peristent volume 
cd /var/www/html

MAINTENANCE="var/.maintenance.flag"
if [ -f "$MAINTENANCE" ]; then
    php bin/magento cache:flush
    php bin/magento maintenance:disable
    touch var/post1.txt
else 
    php bin/magento deploy:mode:set production --skip-compilation
    touch var/post2.txt
fi




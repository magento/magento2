#!/bin/sh

#############################################
#
# Post Release Actions !!!
#
#############################################

#/// Copy the public data from the .new onto the peristent volume 
cd /var/www/html

chgrp -R 82 pub/ var/

MAINTENANCE="var/.maintenance.flag"
if [ -f "$MAINTENANCE" ]; then
    php bin/magento deploy:mode:set production --skip-compilation
    php bin/magento setup:static-content:deploy -f
    php bin/magento setup:di:compile
    php bin/magento cache:flush
    php bin/magento maintenance:disable
    touch var/post1.txt
fi




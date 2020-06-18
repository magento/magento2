#!/bin/sh

#############################################
#
# Pre Release Actions !!!
#
#############################################

#/// Copy the public data from the .new onto the peristent volume 
cd /var/www/html

#/// set maintenance mode + copy some static files into pub
php bin/magento maintenance:enable
cp -a pub.release/* pub/
touch var/pre1.txt

#MODE=$(php bin/magento deploy:mode:show | sed -e 's/\.//g' | awk '{print $4}')
#if [ $MODE != 'production' ]
#then
#    #/// set maintenance mode
#fi




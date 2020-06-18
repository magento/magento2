#!/bin/sh

#############################################
#
# Pre Release Actions !!!
#
#############################################

#/// Copy the public data from the .new onto the peristent volume 
cd /var/www/html

MODE=$(php bin/magento deploy:mode:show | sed -e 's/\.//g' | awk '{print $4}')
if [ $MODE != 'production' ]
then
    #/// set maintenance mode
    php bin/magento maintenance:enable
    touch var/pre1.txt
fi
touch var/pre2.txt
#echo "Release complete ...."




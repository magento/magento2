#!/bin/sh

#############################################
#
# Post Release Actions !!!
#
#############################################

#/// Copy the public data from the .new onto the peristent volume 
cd /var/www/html

SOUCRE="pub.new"
TARGET="pub"
if [ -d "$SOUCRE" ]; then
  cp -a $SOUCRE/* $TARGET/
  #rm -f $SOURCE
  php bin/magento cache:flush
  touch post1.txt
fi
touch post2.txt
php bin/magento maintenance:disable




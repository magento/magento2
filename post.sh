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
  mv $SOUCRE/* $TARGET/
  rm -f $SOURCE
  php bin/magento cache:flush
fi
php bin/magento maintenance:disable




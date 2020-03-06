#!/bin/sh

#/bin/mv "/www/var/magento/"* /var/www/html/

find /var/www/magento  -exec mv -f '{}' /var/www/html/ \;
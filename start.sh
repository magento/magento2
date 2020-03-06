#!/bin/sh

#/bin/mv "/www/var/magento/"* /var/www/html/

find /var/www/magento/* -type f -or -type d -exec mv -f '{}' /var/www/html/ \;
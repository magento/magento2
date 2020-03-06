#!/bin/sh

#/bin/mv "/www/var/magento/"* /var/www/html/

find /var/www/magento -iname '*.*' -exec mv '{}' /var/www/html/ \;
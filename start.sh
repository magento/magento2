#!/bin/sh

find /var/www/magento -type f -or -type d -exec mv -f '{}' /var/www/html/ \;

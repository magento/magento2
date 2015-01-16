#!/bin/sh
# Copyright © 2015 Magento. All rights reserved.
# See COPYING.txt for license details.
# location of the php binary
if [ ! "$1" = "" ] ; then
CRONSCRIPT=$1
else
CRONSCRIPT=pub/cron.php
fi

PHP_BIN=`which php`

# absolute path to magento installation
INSTALLDIR=`echo $0 | sed 's/cron\.sh//g'`"../../"

# prepend the intallation path if not given an absolute path
if [ "$INSTALLDIR" != "" -a "`expr index $CRONSCRIPT /`" != "1" ];then
    if ! ps auxwww | grep "$INSTALLDIR""$CRONSCRIPT" | grep -v grep 1>/dev/null 2>/dev/null ; then
    $PHP_BIN "$INSTALLDIR""$CRONSCRIPT" &
    fi
else
    if  ! ps auxwww | grep " $CRONSCRIPT" | grep -v grep | grep -v cron.sh 1>/dev/null 2>/dev/null ; then
        $PHP_BIN $CRONSCRIPT &
    fi
fi

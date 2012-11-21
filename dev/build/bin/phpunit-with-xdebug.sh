#!/usr/bin/env bash
PHP="/usr/bin/env php "
XDEBUG_INSTALED=`$PHP -m|grep xdebug`
if [ -z "$XDEBUG_INSTALED" ]; then
    # Test xdebug.so exists in 'extension_dir'
    XDEBUG_LIB=`$PHP -r "echo ini_get('extension_dir');"`/xdebug.so
    if [ ! -f $XDEBUG_LIB ];  then
        # Try locate xdebug.so
        XDEBUG_LIB=`locate xdebug.so | head -1`
    fi
    if [ -z "$XDEBUG_LIB" ]; then
        echo XDebug extension is not found [$XDEBUG_LIB]
        exit 1
    fi
    $PHP  -d zend_extension=$XDEBUG_LIB -d xdebug.max_nesting_level=200 `which phpunit` $@
else
    # XDebug is enabled in php.ini
    /usr/bin/env phpunit $@
fi

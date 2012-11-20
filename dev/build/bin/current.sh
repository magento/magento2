#!/bin/sh

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

. $DIR/include.sh

cd $PWD/../

if [ -L "current" ]; then
    log "Removing previous 'current' link..."
    rm current
    check_failure $?
fi

log "Creating 'current' link..."
ln -sf $BUILD_DIR current
check_failure $?

ch_baseurl $DB_NAME $URL_UNSECURE $URL_SECURE
clean_cache $BUILD_DIR

cd $OLDPWD
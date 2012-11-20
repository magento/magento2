#!/bin/sh

OLDPWD="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
OLDIFS=$IFS
PHP_BIN="/usr/bin/php"

DB_HOST="$1"
DB_PORT=3306
DB_NAME="$2"
DB_USER="$3"
DB_PASS="$4"

BUILD_DIR="$5"
PREVIOUS_BUILD_DIR="$6"
URL_UNSECURE="$7"
URL_SECURE="$8"

PWD="$BUILD_DIR"

check_failure() {
    if [ "${1}" -ne "0" ]; then
        cd $OLDPWD
        IFS=$OLDIFS
        failed "ERROR # ${1} : ${2}"
    fi
}

failed() {
   log "$1"
   exit 1
}

log() {
    echo "$1"
}

ch_baseurl() {
    log "Updating unsecure base url..."
    echo "USE $1; UPDATE core_config_data SET value = '$2' WHERE path like 'web/unsecure/base_url';" | mysql -h $DB_HOST -P $DB_PORT -u$DB_USER -p$DB_PASS
    check_failure $?
    log "Updating secure base url..."
    echo "USE $1; UPDATE core_config_data SET value = '$3' WHERE path like 'web/secure/base_url';" | mysql -h $DB_HOST -P $DB_PORT -u$DB_USER -p$DB_PASS
    check_failure $?
}

clean_cache() {
    log "Clearing cache..."
    rm -rf $1/var/cache/*
    check_failure $?
}

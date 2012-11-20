#!/bin/sh

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

. $DIR/include.sh

cd $PREVIOUS_BUILD_DIR
OLD_BUILD_BASE_DIR="$(basename `pwd -P`)"

SB_DB=`grep -R dbname app/etc/local.xml | head -n1 | cut -d "[" -f 3 | cut -d "]" -f 1`

log "Dropping DB if exists..."
echo "DROP DATABASE IF EXISTS \`$DB_NAME\`;" | mysql -h $DB_HOST -P $DB_PORT -u$DB_USER -p$DB_PASS
check_failure $?

log "Creating clean DB..."
echo "CREATE DATABASE \`$DB_NAME\`;" | mysql -h $DB_HOST -P $DB_PORT -u$DB_USER -p$DB_PASS
check_failure $?

echo 'SHOW DATABASES;' | mysql -h $DB_HOST -P $DB_PORT -u$DB_USER -p$DB_PASS | grep $SB_DB > /dev/null
if [ "$?" -eq 0 ] ; then
    log "Copying DB..."
    mysqldump -h $DB_HOST -P $DB_PORT -u$DB_USER -p$DB_PASS $SB_DB | mysql -h $DB_HOST -P $DB_PORT -u$DB_USER -p$DB_PASS $DB_NAME
    check_failure $?
fi

cd $OLDPWD

CURRENT_BUILD_BASE_DIR="$(basename `pwd -P`)"
ch_baseurl $DB_NAME "$URL_UNSECURE/$CURRENT_BUILD_BASE_DIR/" "$URL_SECURE/$CURRENT_BUILD_BASE_DIR/"
ch_baseurl $SB_DB "$URL_UNSECURE/$OLD_BUILD_BASE_DIR/" "$URL_SECURE/$OLD_BUILD_BASE_DIR/"

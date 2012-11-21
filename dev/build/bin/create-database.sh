#!/bin/sh

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

. $DIR/include.sh

log "Creating clean DB..."
echo "CREATE DATABASE \`$DB_NAME\`;" | mysql -h $DB_HOST -P $DB_PORT -u$DB_USER -p$DB_PASS
check_failure $?

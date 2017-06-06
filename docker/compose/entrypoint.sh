apt-get install -y netcat 
while ! nc -z db 3306; do sleep 3; done 
echo "MySql is now running" 
cd /var/www/html 
composer install 
cmd="./bin/magento setup:install "
#./bin/magento setup:install \
if [ -n "$MAGENTO_ADMIN_FIRSTNAME" ]; then
	cmd="$cmd --admin-firstname=$MAGENTO_ADMIN_FIRSTNAME "
fi
if [ -n "$MAGENTO_ADMIN_LASTNAME" ]; then
        cmd="$cmd --admin-lastname=$MAGENTO_ADMIN_LASTNAME "
fi
if [ -n "$MAGENTO_ADMIN_EMAIL" ]; then
        cmd="$cmd --admin-email=$MAGENTO_ADMIN_EMAIL "
fi
if [ -n "$MAGENTO_ADMIN_USER" ]; then
        cmd="$cmd --admin-user=$MAGENTO_ADMIN_USER "
fi
if [ -n "$MAGENTO_ADMIN_PASSWORD" ]; then
        cmd="$cmd --admin-password=$MAGENTO_ADMIN_PASSWORD "
fi
if [ -n "$MAGENTO_BASE_URL" ]; then
        cmd="$cmd --base-url=$MAGENTO_BASE_URL "
fi
if [ -n "$MAGENTO_BACKEND_FRONTNAME" ]; then
        cmd="$cmd --backend-frontname=$MAGENTO_BACKEND_FRONTNAME "
fi
if [ -n "$MAGENTO_DB_HOST" ]; then
        cmd="$cmd --db-host=$MAGENTO_DB_HOST "
fi
if [ -n "$MAGENTO_DB_NAME" ]; then
        cmd="$cmd --db-name=$MAGENTO_DB_NAME "
fi
if [ -n "$MAGENTO_DB_USER" ]; then
        cmd="$cmd --db-user=$MAGENTO_DB_USER "
fi
if [ -n "$MAGENTO_DB_PASSWORD" ]; then
        cmd="$cmd --db-password=$MAGENTO_DB_PASSWORD "
fi
if [ -n "$MAGENTO_DB_PREFIX" ]; then
        cmd="$cmd --db-prefix=$MAGENTO_DB_PREFIX "
fi
if [ -n "$MAGENTO_LANGUAGE" ]; then
        cmd="$cmd --language=$MAGENTO_LANGUAGE "
fi
if [ -n "$MAGENTO_CURRENCY" ]; then
        cmd="$cmd --currency=$MAGENTO_CURRENCY "
fi
if [ -n "$MAGENTO_TIMEZONE" ]; then
        cmd="$cmd --timezone=$MAGENTO_TIMEZONE "
fi
if [ -n "$MAGENTO_USE_REWRITES" ]; then
        cmd="$cmd --use-rewrites=$MAGENTO_USE_REWRITES "
fi
if [ -n "$MAGENTO_USE_SECURE" ]; then
        cmd="$cmd --use-secure=$MAGENTO_USE_SECURE "
fi
if [ -n "$MAGENTO_BASE_URL_SECURE" ]; then
        cmd="$cmd --base-url-secure=$MAGENTO_BASE_URL_SECURE "
fi
if [ -n "$MAGENTO_USE_SECURE_ADMIN" ]; then
        cmd="$cmd --use-secure-admin=$MAGENTO_USE_SECURE_ADMIN "
fi
if [ -n "$MAGENTO_ADMIN_USE_SECURITY_KEY" ]; then
        cmd="$cmd --use-security-key=$MAGENTO_USE_SECURITY_KEY "
fi
if [ -n "$MAGENTO_SESSION_SAVE" ]; then
        cmd="$cmd --session-save=$MAGENTO_SESSION_SAVE "
fi
if [ -n "$MAGENTO_KEY" ]; then
        cmd="$cmd --key=$MAGENTO_KEY "
fi
if [ -n "$MAGENTO_DB_INIT_STATEMENTS" ]; then
        cmd="$cmd --db-init-statements=$MAGENTO_DB_INIT_STATEMENTS "
fi
if [ -n "$MAGENTO_SALES_ORDER_INCREMENT_PREFIX" ]; then
        cmd="$cmd --sales-order-increment-prefix=$MAGENTO_SALES_ORDER_INCREMENT_PREFIX "
fi
eval $cmd
chmod -R 777 .
/bin/sh -c /usr/local/bin/docker-php-entrypoint
/bin/sh -c apache2-foreground

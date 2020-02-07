#!/bin/bash

magento_base_url=http://dev.magento2ce.com/;
magento_admin_user=admin;
magento_admin_password=123123q;

db_name=dev_magento2ce_com;
db_login=developer
db_password=qwerty123

mysql --user=${db_login} --password=${db_password} -e "CREATE DATABASE IF NOT EXISTS ${db_name}";

rm -rf app/etc/env.php;
rm -rf app/etc/config.php;
find var/ -not -name '.htaccess' -delete;
find pub/static/ -not -name '.htaccess' -delete;
find generated/ -not -name '.htaccess' -delete;
#find vendor/ -not -name '.htaccess' -delete;

composer install;

php bin/magento setup:install \
    --base-url=${magento_base_url} \
    --db-host=127.0.0.1 \
    --db-name=${db_name} \
    --db-user=${db_login} \
    --db-password=${db_password} \
    --admin-firstname=Admin \
    --admin-lastname=Adminov \
    --admin-email=admin@admin.com \
    --admin-user=${magento_admin_user} \
    --admin-password=${magento_admin_password} \
    --language=en_US \
    --currency=USD \
    --timezone=America/Chicago \
    --use-rewrites=1 \
    --cleanup-database \
    --backend-frontname=admin
#    --db-prefix=zak
#    --key=d1fa709009a9e31d0a774f67e2049509

#Availabel modes default, developer, or production
bin/magento deploy:mode:set developer;

bin/magento config:set admin/security/admin_account_sharing 1;
bin/magento config:set admin/security/session_lifetime 9000;
bin/magento config:set admin/security/min_time_between_password_reset_requests 0;
bin/magento config:set admin/security/use_form_key 0;
bin/magento config:set system/smtp/disable 1;
bin/magento config:set cms/wysiwyg/enabled "disabled";

bin/magento indexer:reindex;
bin/magento cache:flush;

#sudo chmod 777 -R pub/media pub/static var/ generated/;

#Link EE to CE
#php m2ee/dev/tools/build-ee.php --command link --ce-source /var/www/dev.magento2ce.com --ee-source /var/www/dev.magento2ce.com/m2ee/ --exclude true
#Link B2B to CE
#php m2ee/dev/tools/build-ee.php --command link --ce-source /var/www/dev.magento2ce.com --ee-source /var/www/dev.magento2ce.com/m2b2b/ --exclude true

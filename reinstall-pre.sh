#!/bin/bash

DATABASE_NAME=magento2
DATABASE_USERNAME=root
ADMIN_EMAIL=admin@example.com
ADMIN_USERNAME=admin
ADMIN_PASSWORD=password1
BASE_URL=http://m2.com/

mysql -u $DATABASE_USERNAME  -e "drop database $DATABASE_NAME;"
mysql -u $DATABASE_USERNAME  -e "create database $DATABASE_NAME;"

rm app/etc/env.php app/etc/config.php
rm -rf var/*
rm -rf pub/media/*
rm app/etc/config.php
rm -rf pub/static/*
rm -rf pub/opt/*
rm -rf pub/errors/*

git checkout -- pub

# Should really do a chown to the right user
chmod -R a+wX var
chmod -R a+wX app/etc
chmod -R a+wX pub

composer install

php bin/magento setup:install \
 --db-host=127.0.0.1 \
 --db-name=$DATABASE_NAME \
 --db-user=$DATABASE_USERNAME \
 --base-url=$BASE_URL \
 --language=en_US \
 --timezone=America/Chicago \
 --currency=USD \
 --admin-user=$ADMIN_USERNAME \
 --admin-password=$ADMIN_PASSWORD \
 --admin-email=$ADMIN_EMAIL \
 --admin-firstname=John \
 --admin-lastname=Doe \
 --cleanup-database \
 --backend-frontname=admin \
 --use-rewrites=1 \
 --db-prefix=zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz
# --use-sample-data=1

# Should really do a chown to the right user
chmod -R a+wX var
chmod -R a+wX app/etc
chmod -R a+wX pub


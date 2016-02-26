#!/usr/bin/env bash

# Copyright © 2015 Magento. All rights reserved.
# See COPYING.txt for license details.

# prefer our cached binaries
export PATH="$HOME/.cache/bin:$PATH"

# mock mail
sudo service postfix stop
smtp-sink -d "%d.%H.%M.%S" localhost:2500 1000 &
echo 'sendmail_path = "/usr/sbin/sendmail -t -i "' > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/sendmail.ini

# disable xDebug
echo > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini

# create database for integration tests
case $TEST_SUITE in
    integration_part_1|integration_part_2)
        ./dev/tests/integration/IntegationTestsForTravis.sh 2
        ;&  # intentional fallthrough
    integration_integrity)
        mysql -uroot -e 'SET @@global.sql_mode = NO_ENGINE_SUBSTITUTION; CREATE DATABASE magento_integration_tests;'
        mv dev/tests/integration/etc/install-config-mysql.travis.php.dist \
            dev/tests/integration/etc/install-config-mysql.php
        ;;
esac

# change memory_limit for travis
echo 'memory_limit = -1' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
phpenv rehash;

# install deps
composer install --no-interaction --prefer-dist

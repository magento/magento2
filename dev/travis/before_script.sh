#!/usr/bin/env bash

# Copyright © 2015 Magento. All rights reserved.
# See COPYING.txt for license details.

set -e
export PATH="$HOME/.cache/bin:$PATH"

# mock mail
sudo service postfix stop
smtp-sink -d "%d.%H.%M.%S" localhost:2500 1000 &
echo 'sendmail_path = "/usr/sbin/sendmail -t -i "' > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/sendmail.ini

# disable xDebug
echo > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini

# prepare for integration tests
case $TEST_SUITE in
    integration)
        cd dev/tests/integration

        test_set_list=$(find testsuite/* -maxdepth 1 -mindepth 1 -type d)
        test_set_size=$(($(printf "$test_set_list" | wc -l)/INTEGRATION_SETS))

        # create n testsuites
        for i in $(seq 1 $INTEGRATION_SETS); do
            cp phpunit.xml.dist phpunit.xml.travis$i
        done

        # remove memory usage and update integration tests from all except testsuite 1
        for i in $(seq 2 $INTEGRATION_SETS); do
            perl -pi -0e 's#^\s+<!-- Memory(.*?)</testsuite>\n##ims' phpunit.xml.travis$i
            perl -pi -e 's#\s+<directory.*>../../../update/dev/tests.*</directory>\n##g' phpunit.xml.travis$i
        done

        # create list of folders on which tests are to be run
        i=0; j=1
        for test_set in $test_set_list; do
            test_xml[j]+="            <directory suffix=\"Test.php\">$test_set</directory>\n"
            
            i=$((i+1))
            if [ $i -eq $test_set_size ] && [ $j -lt $INTEGRATION_SETS ]; then
                j=$((j+1))
                i=0
            fi
        done

        # Finally replacing in config files.
        for i in `seq 1 $INTEGRATION_SETS`; do
            perl -pi -e "s#\s+<directory.*>testsuite</directory>#${test_xml[i]}#g" phpunit.xml.travis$i
        done

        cd ../../..
        ;&  # intentional fallthrough
    integration_integrity)
        mysql -uroot -e '
            SET @@global.sql_mode = NO_ENGINE_SUBSTITUTION;
            CREATE DATABASE magento_integration_tests;
        '
        mv dev/tests/integration/etc/install-config-mysql.travis.php.dist \
            dev/tests/integration/etc/install-config-mysql.php
        ;;
esac

# change memory_limit for travis
echo 'memory_limit = -1' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
phpenv rehash;

# install deps
composer install --no-interaction --prefer-dist

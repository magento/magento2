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

        test_set_list=$(find -s testsuite/* -maxdepth 1 -mindepth 1 -type d)
        test_set_size=$(($(printf "$test_set_list" | wc -l)/INTEGRATION_SETS))

        echo "==> preparing integration testsuite on index $INTEGRATION_INDEX of set size $INTEGRATION_SETS"
        cp phpunit.xml.dist phpunit.xml

        # remove memory usage and update integration tests if not set 1
        if [[ $INTEGRATION_INDEX > 1 ]]; then
            echo "  - excluding testsuite/Magento/MemoryUsageTest.php"
            perl -pi -0e 's#^\s+<!-- Memory(.*?)</testsuite>\n##ims' phpunit.xml
            
            echo "  - excluding ../../../update/dev/tests/integration/testsuite"
            perl -pi -e 's#\s+<directory.*>../../../update/dev/tests.*</directory>\n##g' phpunit.xml
        fi

        # divide test sets up by indexed testsuites
        i=0; j=1
        for test_set in $test_set_list; do
            test_xml[j]+="            <directory suffix=\"Test.php\">$test_set</directory>\n"

            if [[ $j -eq $INTEGRATION_INDEX ]]; then
                echo "  + including $test_set"
            else
                echo "  - excluding $test_set"
            fi

            i=$((i+1))
            if [ $i -eq $test_set_size ] && [ $j -lt $INTEGRATION_SETS ]; then
                j=$((j+1))
                i=0
            fi
        done

        # replace test sets for current index into testsuite
        perl -pi -e "s#\s+<directory.*>testsuite</directory>#${test_xml[INTEGRATION_INDEX]}#g" phpunit.xml

        echo "==> testsuite preparation complete"

        # create database and move db config into place
        mysql -uroot -e '
            SET @@global.sql_mode = NO_ENGINE_SUBSTITUTION;
            CREATE DATABASE magento_integration_tests;
        '
        mv etc/install-config-mysql.travis.php.dist etc/install-config-mysql.php

        cd ../../..
        ;;
esac

# change memory_limit for travis
echo 'memory_limit = -1' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
phpenv rehash;

# install deps
if [[ -n "$GITHUB_TOKEN" ]]; then
    composer config github-oauth.github.com "$GITHUB_TOKEN"
fi
composer install --no-interaction --prefer-dist

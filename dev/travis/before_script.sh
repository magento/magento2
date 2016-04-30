#!/usr/bin/env bash

# Copyright © 2016 Magento. All rights reserved.
# See COPYING.txt for license details.

set -e
PATH="$HOME/.cache/bin:$PATH"

trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

# mock mail
sudo service postfix stop
echo # print a newline
smtp-sink -d "%d.%H.%M.%S" localhost:2500 1000 &
echo 'sendmail_path = "/usr/sbin/sendmail -t -i "' > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/sendmail.ini

# disable xDebug
echo > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini

# prepare for integration tests
case $TEST_SUITE in
    integration)
        cd dev/tests/integration

        test_set_list=$(find testsuite/* -maxdepth 1 -mindepth 1 -type d | sort)
        test_set_size=$(($(printf "$test_set_list" | wc -l)/INTEGRATION_SETS))

        echo "==> preparing integration testsuite on index $INTEGRATION_INDEX with set size of $test_set_size"
        cp phpunit.xml.dist phpunit.xml

        # remove memory usage tests if from any set other than the first
        if [[ $INTEGRATION_INDEX > 1 ]]; then
            echo "  - removing testsuite/Magento/MemoryUsageTest.php"
            perl -pi -0e 's#^\s+<!-- Memory(.*?)</testsuite>\n##ims' phpunit.xml
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
    static)
        cd dev/tests/static

        echo "==> preparing changed files list"
        changed_files_ce="$TRAVIS_BUILD_DIR/dev/tests/static/testsuite/Magento/Test/_files/changed_files_ce.txt"
        php get_github_changes.php \
            --output-file="$changed_files_ce" \
            --base-path="$TRAVIS_BUILD_DIR" \
            --repo='https://github.com/magento/magento2.git' \
            --branch='develop'
        cat "$changed_files_ce" | sed 's/^/  + including /'

        cd ../../..
        ;;
esac

# change memory_limit for travis
echo 'memory_limit = -1' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
phpenv rehash;

# install deps
export COMPOSER_BIN_DIR=~/bin
if [[ -n "$GITHUB_TOKEN" ]]; then
    composer config github-oauth.github.com "$GITHUB_TOKEN"
fi
composer install --no-interaction --prefer-dist

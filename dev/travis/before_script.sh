#!/usr/bin/env bash

# Copyright © 2013-2017 Magento, Inc. All rights reserved.
# See COPYING.txt for license details.

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

# prepare for test suite
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
            --branch='2.1'
        cat "$changed_files_ce" | sed 's/^/  + including /'

        cd ../../..
        ;;
esac

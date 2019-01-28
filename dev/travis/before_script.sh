#!/usr/bin/env bash

# Copyright © Magento, Inc. All rights reserved.
# See COPYING.txt for license details.

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

# prepare for test suite
case $TEST_SUITE in
    integration)
        cd dev/tests/integration

        test_set_list=$(find testsuite/* -maxdepth 1 -mindepth 1 -type d | sort)
        test_set_count=$(printf "$test_set_list" | wc -l)
        test_set_size[1]=$(printf "%.0f" $(echo "$test_set_count*0.12" | bc))  #12%
        test_set_size[2]=$(printf "%.0f" $(echo "$test_set_count*0.32" | bc))  #32%
        test_set_size[3]=$((test_set_count-test_set_size[1]-test_set_size[2])) #56%
        echo "Total = ${test_set_count}; Batch #1 = ${test_set_size[1]}; Batch #2 = ${test_set_size[2]}; Batch #3 = ${test_set_size[3]};";

        echo "==> preparing integration testsuite on index $INTEGRATION_INDEX with set size of ${test_set_size[$INTEGRATION_INDEX]}"
        cp phpunit.xml.dist phpunit.xml

        # remove memory usage tests if from any set other than the first
        if [[ $INTEGRATION_INDEX > 1 ]]; then
            echo "  - removing testsuite/Magento/MemoryUsageTest.php"
            perl -pi -0e 's#^\s+<!-- Memory(.*?)</testsuite>\n##ims' phpunit.xml
        fi

        # divide test sets up by indexed testsuites
        i=0; j=1; dirIndex=1; testIndex=1;
        for test_set in $test_set_list; do
            test_xml[j]+="            <directory suffix=\"Test.php\">$test_set</directory>\n"

            if [[ $j -eq $INTEGRATION_INDEX ]]; then
                echo "$dirIndex: Batch #$j($testIndex of ${test_set_size[$j]}): + including $test_set"
            else
                echo "$dirIndex: Batch #$j($testIndex of ${test_set_size[$j]}): + excluding $test_set"
            fi

            testIndex=$((testIndex+1))
            dirIndex=$((dirIndex+1))
            i=$((i+1))
            if [ $i -eq ${test_set_size[$j]} ] && [ $j -lt $INTEGRATION_SETS ]; then
                j=$((j+1))
                i=0
                testIndex=1
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
            --branch="$TRAVIS_BRANCH"
        sed 's/^/  + including /' "$changed_files_ce"

        cd ../../..
        ;;
    js)
        cp package.json.sample package.json
        cp Gruntfile.js.sample Gruntfile.js
        yarn

        if [[ $GRUNT_COMMAND != "static" ]]; then
            echo "Installing Magento"
            mysql -uroot -e 'CREATE DATABASE magento2;'
            php bin/magento setup:install -q \
                --admin-user="admin" \
                --admin-password="123123q" \
                --admin-email="admin@example.com" \
                --admin-firstname="John" \
                --admin-lastname="Doe"

            echo "Deploying Static Content"
            php bin/magento setup:static-content:deploy -f -q -j=2 \
                --no-css --no-less --no-images --no-fonts --no-misc --no-html-minify
        fi
        ;;
    functional)
        echo "Installing Magento"
        mysql -uroot -e 'CREATE DATABASE magento2;'
        php bin/magento setup:install -q \
            --language="en_US" \
            --timezone="UTC" \
            --currency="USD" \
            --base-url="http://${MAGENTO_HOST_NAME}/" \
            --admin-firstname="John" \
            --admin-lastname="Doe" \
            --backend-frontname="backend" \
            --admin-email="admin@example.com" \
            --admin-user="admin" \
            --use-rewrites=1 \
            --admin-use-security-key=0 \
            --admin-password="123123q"

        echo "Enabling production mode"
        php bin/magento deploy:mode:set production

        echo "Prepare functional tests for running"
        cd dev/tests/functional

        composer install && composer require se/selenium-server-standalone:2.53.1
        export DISPLAY=:1.0
        sh ./vendor/se/selenium-server-standalone/bin/selenium-server-standalone -port 4444 -host 127.0.0.1 \
            -Dwebdriver.firefox.bin=$(which firefox) -trustAllSSLCertificate &> ~/selenium.log &

        cp ./phpunit.xml.dist ./phpunit.xml
        sed -e "s?127.0.0.1?${MAGENTO_HOST_NAME}?g" --in-place ./phpunit.xml
        sed -e "s?basic?travis_acceptance?g" --in-place ./phpunit.xml
        cp ./.htaccess.sample ./.htaccess
        cd ./utils
        php -f generate/moduleSequence.php
        php -f mtf troubleshooting:check-all

        cd ../../..
        ;;
esac

#!/usr/bin/env bash

# Copyright © Magento, Inc. All rights reserved.
# See COPYING.txt for license details.

case $TEST_SUITE in
    static)
        TEST_FILTER='--filter "Magento\\Test\\Php\\LiveCodeTest"' || true
        phpunit -c dev/tests/$TEST_SUITE $TEST_FILTER
        grunt static
        ;;
    js)
        grunt spec
        ;;
    functional)
        cd dev/tests/functional
        vendor/phpunit/phpunit/phpunit -c phpunit.xml --debug testsuites/Magento/Mtf/TestSuite/InjectableTests.php
        ;;
    *)
        phpunit -c dev/tests/$TEST_SUITE $TEST_FILTER;
        ;;
esac

#!/usr/bin/env bash

# Copyright © 2015 Magento. All rights reserved.
# See COPYING.txt for license details.

set -e

case $TEST_SUITE in
    unit)
        cd dev/tests/unit
        phpunit
        ;;
    integration)
        cd dev/tests/integration
        phpunit
        ;;
    static)
        cd dev/tests/static
        php get_github_changes.php \
            --output-file='$TRAVIS_BUILD_DIR/dev/tests/static/testsuite/Magento/Test/_files/changed_files_ce.txt' \
            --base-path='$TRAVIS_BUILD_DIR' \
            --repo='https://github.com/magento/magento2.git' \
            --branch='develop'
        phpunit --filter 'Magento\\Test\\Php\\LiveCodeTest'
        ;;
esac

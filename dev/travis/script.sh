#!/usr/bin/env bash

# Copyright © 2016 Magento. All rights reserved.
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
        phpunit --filter 'Magento\\Test\\Php\\LiveCodeTest'
        ;;
esac

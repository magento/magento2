#!/usr/bin/env bash

# Copyright © 2015 Magento. All rights reserved.
# See COPYING.txt for license details.

set -e
export PATH="~/bin:$PATH"

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
        phpunit --filter 'Magento\\Test\\Php\\LiveCodeTest::(testCodeStyle|testAnnotationStandard)'
        ;;
esac

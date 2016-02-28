#!/usr/bin/env bash

# Copyright © 2015 Magento. All rights reserved.
# See COPYING.txt for license details.

set -e
export PATH="./../../../vendor/bin:$PATH"

case $TEST_SUITE in
    unit)
        cd dev/tests/unit
        phpunit -c phpunit.xml.dist
        ;;
    integration)
        cd dev/tests/integration
        phpunit -c phpunit.xml.travis$INTEGRATION_INDEX
        ;;
    static)
        cd dev/tests/static
        phpunit -c phpunit.xml.dist --filter 'Magento\\Test\\Php\\LiveCodeTest::(testCodeStyle|testAnnotationStandard)'
        ;;
esac

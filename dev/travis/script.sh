#!/usr/bin/env bash

# Copyright © 2015 Magento. All rights reserved.
# See COPYING.txt for license details.

PATH="./../../../vendor/bin:$PATH"

case $TEST_SUITE in
    unit)
        cd dev/tests/unit
        phpunit -c phpunit.xml.dist
        ;;
    integration_part_1)
        cd dev/tests/integration
        phpunit -c phpunit.xml.travis1
        ;;
    integration_part_2)
        cd dev/tests/integration
        phpunit -c phpunit.xml.travis2
        ;;
    integration_integrity)
        cd dev/tests/integration
        phpunit -c phpunit.xml.dist testsuite/Magento/Test/Integrity
        ;;
    static_phpcs)
        cd dev/tests/static
        phpunit -c phpunit.xml.dist --filter 'Magento\\Test\\Php\\LiveCodeTest::testCodeStyle'
        ;;
    static_annotation)
        cd dev/tests/static
        phpunit -c phpunit.xml.dist --filter 'Magento\\Test\\Php\\LiveCodeTest::testAnnotationStandard'
        ;;
esac

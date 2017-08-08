<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Assert;

/**
 * Check that actual data contains all values from expected data
 * But actual data can have more values than expected data
 */
class AssertArrayContains
{
    /**
     * @param array $expected
     * @param array $actual
     * @return void
     */
    public static function assert(array $expected, array $actual)
    {
        foreach ($expected as $key => $value) {
            \PHPUnit_Framework_Assert::assertArrayHasKey(
                $key,
                $actual,
                "Expected value for key '{$key}' is missed"
            );
            if (is_array($value)) {
                self::assert($value, $actual[$key]);
            } else {
                \PHPUnit_Framework_Assert::assertEquals(
                    $value,
                    $actual[$key],
                    "Expected value for key '{$key}' doesn't match"
                );
            }
        }
    }
}

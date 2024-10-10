<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Math\Test\Unit;

use Magento\Framework\Math\FloatComparator;
use PHPUnit\Framework\TestCase;

class FloatComparatorTest extends TestCase
{
    /**
     * @var FloatComparator
     */
    private $comparator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->comparator = new FloatComparator();
    }

    /**
     * Checks a case when `a` and `b` are equal.
     *
     * @param float $a
     * @param float $b
     * @param bool $expected
     * @dataProvider eqDataProvider
     */
    public function testEq(float $a, float $b, bool $expected)
    {
        self::assertEquals($expected, $this->comparator->equal($a, $b));
    }

    /**
     * Gets list of variations to compare equal float.
     *
     * @return array
     */
    public static function eqDataProvider(): array
    {
        return [
            [10, 10.00001, true],
            [10, 10.000001, true],
            [10.0000099, 10.00001, true],
            [1, 1.0001, false],
            [1, -1.00001, false],
        ];
    }

    /**
     * Checks a case when `a` > `b`.
     *
     * @param float $a
     * @param float $b
     * @param bool $expected
     * @dataProvider gtDataProvider
     */
    public function testGt(float $a, float $b, bool $expected)
    {
        self::assertEquals($expected, $this->comparator->greaterThan($a, $b));
    }

    /**
     * Gets list of variations to compare if `a` > `b`.
     *
     * @return array
     */
    public static function gtDataProvider(): array
    {
        return [
            [10, 10.00001, false],
            [10, 10.000001, false],
            [10.0000099, 10.00001, false],
            [1.0001, 1, true],
            [1, -1.00001, true],
        ];
    }

    /**
     * Checks a case when `a` >= `b`.
     *
     * @param float $a
     * @param float $b
     * @param bool $expected
     * @dataProvider gteDataProvider
     */
    public function testGte(float $a, float $b, bool $expected)
    {
        self::assertEquals($expected, $this->comparator->greaterThanOrEqual($a, $b));
    }

    /**
     * Gets list of variations to compare if `a` >= `b`.
     *
     * @return array
     */
    public static function gteDataProvider(): array
    {
        return [
            [10, 10.00001, true],
            [10, 10.000001, true],
            [10.0000099, 10.00001, true],
            [1.0001, 1, true],
            [1, -1.00001, true],
            [1.0001, 1.001, false],
        ];
    }
}

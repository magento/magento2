<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Math\Test\Unit;

use Magento\Framework\Math\Division;
use PHPUnit\Framework\TestCase;

class DivisionTest extends TestCase
{
    /**
     * @var float
     */
    private const EPSILON = 0.0000000001;

    /**
     * @dataProvider getExactDivisionDataProvider
     */
    public function testGetExactDivision($dividend, $divisor, $expected)
    {
        $mathDivision = new Division();
        $remainder = $mathDivision->getExactDivision($dividend, $divisor);
        $this->assertEqualsWithDelta($expected, $remainder, self::EPSILON);
    }

    /**
     * @return array
     */
    public function getExactDivisionDataProvider()
    {
        return [
            [17, 3 , 2],
            [7.7, 2 , 1.7],
            [17.8, 3.2 , 1.8],
            [11.7, 1.7 , 1.5],
            [8, 2, 0]
        ];
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Math\Test\Unit;

class DivisionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getExactDivisionDataProvider
     */
    public function testGetExactDivision($dividend, $divisor, $expected)
    {
        $mathDivision = new \Magento\Framework\Math\Division();
        $remainder = $mathDivision->getExactDivision($dividend, $divisor);
        $this->assertEquals($expected, $remainder);
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

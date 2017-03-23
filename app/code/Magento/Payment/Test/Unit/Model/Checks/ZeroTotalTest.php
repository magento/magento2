<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Checks;

use \Magento\Payment\Model\Checks\ZeroTotal;

class ZeroTotalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider paymentMethodDataProvider
     * @param string $code
     * @param int $total
     * @param bool $expectation
     */
    public function testIsApplicable($code, $total, $expectation)
    {
        $paymentMethod = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        if (!$total) {
            $paymentMethod->expects($this->once())
                ->method('getCode')
                ->will($this->returnValue($code));
        }

        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseGrandTotal', '__wakeup'])
            ->getMock();

        $quote->expects($this->once())
            ->method('getBaseGrandTotal')
            ->will($this->returnValue($total));

        $model = new ZeroTotal();
        $this->assertEquals($expectation, $model->isApplicable($paymentMethod, $quote));
    }

    /**
     * @return array
     */
    public function paymentMethodDataProvider()
    {
        return [['not_free', 0, false], ['free', 1, true]];
    }
}

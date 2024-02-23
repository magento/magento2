<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Checks;

use Magento\Payment\Model\Checks\ZeroTotal;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

class ZeroTotalTest extends TestCase
{
    /**
     * @dataProvider paymentMethodDataProvider
     * @param string $code
     * @param int $total
     * @param bool $expectation
     */
    public function testIsApplicable($code, $total, $expectation)
    {
        $paymentMethod = $this->getMockBuilder(MethodInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        if (!$total) {
            $paymentMethod->expects($this->once())
                ->method('getCode')
                ->willReturn($code);
        }

        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['getBaseGrandTotal'])
            ->onlyMethods(['__wakeup'])
            ->getMock();

        $quote->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn($total);

        $model = new ZeroTotal();
        $this->assertEquals($expectation, $model->isApplicable($paymentMethod, $quote));
    }

    /**
     * @return array
     */
    public static function paymentMethodDataProvider()
    {
        return [['not_free', 0, false], ['free', 1, true]];
    }
}

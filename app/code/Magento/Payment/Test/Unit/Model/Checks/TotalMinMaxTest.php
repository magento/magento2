<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Checks;

use Magento\Payment\Model\Checks\TotalMinMax;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

class TotalMinMaxTest extends TestCase
{
    /**
     * Payment min total value
     */
    const PAYMENT_MIN_TOTAL = 2;

    /**
     * Payment max total value
     */
    const PAYMENT_MAX_TOTAL = 5;

    /**
     * @dataProvider paymentMethodDataProvider
     * @param int $baseGrandTotal
     * @param bool $expectation
     */
    public function testIsApplicable($baseGrandTotal, $expectation)
    {
        $paymentMethod = $this->getMockBuilder(
            MethodInterface::class
        )->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $paymentMethod->expects($this->at(0))->method('getConfigData')->with(
            TotalMinMax::MIN_ORDER_TOTAL
        )->willReturn(self::PAYMENT_MIN_TOTAL);
        $paymentMethod->expects($this->at(1))->method('getConfigData')->with(
            TotalMinMax::MAX_ORDER_TOTAL
        )->willReturn(self::PAYMENT_MAX_TOTAL);

        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['getBaseGrandTotal', '__wakeup']
            )->getMock();
        $quote->expects($this->once())->method('getBaseGrandTotal')->willReturn($baseGrandTotal);

        $model = new TotalMinMax();
        $this->assertEquals($expectation, $model->isApplicable($paymentMethod, $quote));
    }

    /**
     * @return array
     */
    public function paymentMethodDataProvider()
    {
        return [[1, false], [6, false], [3, true]];
    }
}

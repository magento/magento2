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
    public const PAYMENT_MIN_TOTAL = 2;

    /**
     * Payment max total value
     */
    public const PAYMENT_MAX_TOTAL = 5;

    /**
     * @dataProvider paymentMethodDataProvider
     * @param int $baseGrandTotal
     * @param bool $expectation
     *
     * @return void
     */
    public function testIsApplicable(int $baseGrandTotal, bool $expectation): void
    {
        $paymentMethod = $this->getMockBuilder(MethodInterface::class)->disableOriginalConstructor()
            ->addMethods([])->getMock();
        $paymentMethod
            ->method('getConfigData')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [TotalMinMax::MIN_ORDER_TOTAL] => self::PAYMENT_MIN_TOTAL,
                [TotalMinMax::MAX_ORDER_TOTAL] => self::PAYMENT_MAX_TOTAL
            });

        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()
            ->onlyMethods(['__wakeup'])
            ->addMethods(['getBaseGrandTotal'])->getMock();
        $quote->expects($this->once())->method('getBaseGrandTotal')->willReturn($baseGrandTotal);

        $model = new TotalMinMax();
        $this->assertEquals($expectation, $model->isApplicable($paymentMethod, $quote));
    }

    /**
     * @return array
     */
    public static function paymentMethodDataProvider(): array
    {
        return [[1, false], [6, false], [3, true]];
    }
}

<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Checks;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Payment\Model\Checks\TotalMinMax;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TotalMinMaxTest extends TestCase
{
    use MockCreationTrait;
    /**
     * Payment min total value
     */
    public const PAYMENT_MIN_TOTAL = 2;

    /**
     * Payment max total value
     */
    public const PAYMENT_MAX_TOTAL = 5;

    /**
     * @param int $baseGrandTotal
     * @param bool $expectation
     *
     * @return void
     */
    #[DataProvider('paymentMethodDataProvider')]
    public function testIsApplicable(int $baseGrandTotal, bool $expectation): void
    {
        $paymentMethod = $this->createMock(MethodInterface::class);
        $paymentMethod
            ->method('getConfigData')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [TotalMinMax::MIN_ORDER_TOTAL] => self::PAYMENT_MIN_TOTAL,
                [TotalMinMax::MAX_ORDER_TOTAL] => self::PAYMENT_MAX_TOTAL
            });

        $quote = $this->createPartialMockWithReflection(
            Quote::class,
            ['__wakeup', 'getBaseGrandTotal']
        );
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

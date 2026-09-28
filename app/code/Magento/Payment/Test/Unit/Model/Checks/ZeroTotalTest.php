<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Checks;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Payment\Model\Checks\ZeroTotal;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ZeroTotalTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @param string $code
     * @param int $total
     * @param bool $expectation
     */
    #[DataProvider('paymentMethodDataProvider')]
    public function testIsApplicable($code, $total, $expectation)
    {
        $paymentMethod = $this->createMock(MethodInterface::class);

        if (!$total) {
            $paymentMethod->expects($this->once())
                ->method('getCode')
                ->willReturn($code);
        }

        $quote = $this->createPartialMockWithReflection(
            Quote::class,
            ['getBaseGrandTotal', '__wakeup']
        );

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

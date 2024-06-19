<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Checks;

use Magento\Payment\Model\Checks\CanUseCheckout;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

class CanUseCheckoutTest extends TestCase
{
    /**
     * @var CanUseCheckout
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new CanUseCheckout();
    }

    /**
     * @dataProvider paymentMethodDataProvider
     * @param bool $expectation
     */
    public function testIsApplicable($expectation)
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()->getMock();
        $paymentMethod = $this->getMockBuilder(
            MethodInterface::class
        )->disableOriginalConstructor()->getMock();
        $paymentMethod->expects($this->once())->method('canUseCheckout')->willReturn(
            $expectation
        );
        $this->assertEquals($expectation, $this->_model->isApplicable($paymentMethod, $quote));
    }

    /**
     * @return array
     */
    public static function paymentMethodDataProvider()
    {
        return [[true], [false]];
    }
}

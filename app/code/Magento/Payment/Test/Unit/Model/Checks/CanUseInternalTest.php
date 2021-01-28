<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Checks;

use \Magento\Payment\Model\Checks\CanUseInternal;

class CanUseInternalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CanUseInternal
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new CanUseInternal();
    }

    /**
     * @dataProvider paymentMethodDataProvider
     * @param bool $expectation
     */
    public function testIsApplicable($expectation)
    {
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $paymentMethod = $this->getMockBuilder(
            \Magento\Payment\Model\MethodInterface::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $paymentMethod->expects($this->once())->method('canUseInternal')->willReturn(
            $expectation
        );
        $this->assertEquals($expectation, $this->_model->isApplicable($paymentMethod, $quote));
    }

    /**
     * @return array
     */
    public function paymentMethodDataProvider()
    {
        return [[true], [false]];
    }
}

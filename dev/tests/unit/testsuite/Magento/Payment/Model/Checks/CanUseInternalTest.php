<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model\Checks;

class CanUseInternalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CanUseInternal
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new CanUseInternal();
    }

    /**
     * @dataProvider paymentMethodDataProvider
     * @param bool $expectation
     */
    public function testIsApplicable($expectation)
    {
        $quote = $this->getMockBuilder('Magento\Sales\Model\Quote')->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $paymentMethod = $this->getMockBuilder(
            'Magento\Payment\Model\Checks\PaymentMethodChecksInterface'
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $paymentMethod->expects($this->once())->method('canUseInternal')->will(
            $this->returnValue($expectation)
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

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Checks;

use \Magento\Payment\Model\Checks\CanUseInternal;

class CanUseInternalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CanUseInternal
     */
    protected $_model;

    protected function setUp()
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

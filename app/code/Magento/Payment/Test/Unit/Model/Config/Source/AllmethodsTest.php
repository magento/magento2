<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Config\Source;

use \Magento\Payment\Model\Config\Source\Allmethods;

class AllmethodsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_paymentData;

    /**
     * @var Allmethods
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_paymentData = $this->getMockBuilder(
            \Magento\Payment\Helper\Data::class
        )->disableOriginalConstructor()->setMethods([])->getMock();

        $this->_model = new Allmethods($this->_paymentData);
    }

    public function testToOptionArray()
    {
        $expectedArray = ['key' => 'value'];
        $this->_paymentData->expects($this->once())
            ->method('getPaymentMethodList')
            ->with(true, true, true)
            ->willReturn($expectedArray);
        $this->assertEquals($expectedArray, $this->_model->toOptionArray());
    }
}

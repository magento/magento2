<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Config\Source;

use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Config\Source\Allmethods;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AllmethodsTest extends TestCase
{
    /**
     * Payment data
     *
     * @var Data|MockObject
     */
    protected $_paymentData;

    /**
     * @var Allmethods
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_paymentData = $this->getMockBuilder(
            Data::class
        )->disableOriginalConstructor()->getMock();

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

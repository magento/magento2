<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
    private $paymentDataMock;

    /**
     * @var Allmethods
     */
    private $model;

    protected function setUp()
    {
        $this->paymentDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->model = new Allmethods($this->paymentDataMock);
    }

    public function testToOptionArray()
    {
        $expectedArray = ['key' => 'value'];
        $this->paymentDataMock->expects($this->once())
            ->method('getPaymentMethodList')
            ->with(true, true, true)
            ->will($this->returnValue($expectedArray));
        $this->assertEquals($expectedArray, $this->model->toOptionArray());
    }
}

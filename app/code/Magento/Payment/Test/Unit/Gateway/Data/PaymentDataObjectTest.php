<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Data;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Model\InfoInterface;

/**
 * Class PaymentDataObjectTest
 */
class PaymentDataObjectTest extends \PHPUnit_Framework_TestCase
{
    /** @var PaymentDataObject */
    protected $model;

    /**
     * @var OrderAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var InfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    protected function setUp()
    {
        $this->orderMock = $this->getMockBuilder('Magento\Payment\Gateway\Data\OrderAdapterInterface')
            ->getMockForAbstractClass();

        $this->paymentMock = $this->getMockBuilder('Magento\Payment\Model\InfoInterface')
            ->getMockForAbstractClass();

        $this->model = new PaymentDataObject($this->orderMock, $this->paymentMock);
    }

    public function testGetOrder()
    {
        $this->assertSame($this->orderMock, $this->model->getOrder()) ;
    }

    public function testGetPayment()
    {
        $this->assertSame($this->paymentMock, $this->model->getPayment()) ;
    }
}

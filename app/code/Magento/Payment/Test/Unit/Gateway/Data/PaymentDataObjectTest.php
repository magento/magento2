<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Data;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Model\InfoInterface;

/**
 * Class PaymentDataObjectTest
 */
class PaymentDataObjectTest extends \PHPUnit\Framework\TestCase
{
    /** @var PaymentDataObject */
    protected $model;

    /**
     * @var OrderAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderMock;

    /**
     * @var InfoInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentMock;

    protected function setUp(): void
    {
        $this->orderMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\OrderAdapterInterface::class)
            ->getMockForAbstractClass();

        $this->paymentMock = $this->getMockBuilder(\Magento\Payment\Model\InfoInterface::class)
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

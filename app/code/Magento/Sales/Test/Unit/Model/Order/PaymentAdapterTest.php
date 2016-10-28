<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

/**
 * Unit test for payment adapter.
 */
class PaymentAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\PaymentAdapter
     */
    private $subject;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Sales\Api\Data\CreditmemoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoMock;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceMock;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\PayOperation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payOperationMock;

    protected function setUp()
    {
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditmemoMock = $this->getMockBuilder(\Magento\Sales\Api\Data\CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->invoiceMock = $this->getMockBuilder(\Magento\Sales\Api\Data\InvoiceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->payOperationMock =$this->getMockBuilder(\Magento\Sales\Model\Order\Invoice\PayOperation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = new \Magento\Sales\Model\Order\PaymentAdapter(
            $this->payOperationMock
        );
    }

    public function testPay()
    {
        $isOnline = true;

        $this->payOperationMock->expects($this->once())
            ->method('execute')
            ->with($this->orderMock, $this->invoiceMock, $isOnline)
            ->willReturn($this->orderMock);

        $this->assertEquals(
            $this->orderMock,
            $this->subject->pay(
                $this->orderMock,
                $this->invoiceMock,
                $isOnline
            )
        );
    }
}

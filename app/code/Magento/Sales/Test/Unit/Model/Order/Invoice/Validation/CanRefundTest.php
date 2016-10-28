<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Invoice\Validation;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class CanRefundTest
 */
class CanRefundTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderPaymentRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMock;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\Validation\CanRefund
     */
    private $validator;

    protected function setUp()
    {
        $this->invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderPaymentRepositoryMock = $this->getMockBuilder(
            \Magento\Sales\Api\OrderPaymentRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->orderRepositoryMock = $this->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->paymentMock = $this->getMockBuilder(\Magento\Payment\Model\InfoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->validator = new \Magento\Sales\Model\Order\Invoice\Validation\CanRefund(
            $this->orderPaymentRepositoryMock,
            $this->orderRepositoryMock
        );
    }

    public function testValidateWrongInvoiceState()
    {
        $this->invoiceMock->expects($this->exactly(2))
            ->method('getState')
            ->willReturnOnConsecutiveCalls(
                \Magento\Sales\Model\Order\Invoice::STATE_OPEN,
                \Magento\Sales\Model\Order\Invoice::STATE_CANCELED
            );
        $this->assertEquals(
            [__('We can\'t create creditmemo for the invoice.')],
            $this->validator->validate($this->invoiceMock)
        );
        $this->assertEquals(
            [__('We can\'t create creditmemo for the invoice.')],
            $this->validator->validate($this->invoiceMock)
        );
    }

    public function testValidateInvoiceSumWasRefunded()
    {
        $this->invoiceMock->expects($this->once())
            ->method('getState')
            ->willReturn(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
        $this->invoiceMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn(1);
        $this->invoiceMock->expects($this->once())
            ->method('getBaseTotalRefunded')
            ->willReturn(1);
        $this->assertEquals(
            [__('We can\'t create creditmemo for the invoice.')],
            $this->validator->validate($this->invoiceMock)
        );
    }

    public function testValidate()
    {
        $this->invoiceMock->expects($this->once())
            ->method('getState')
            ->willReturn(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
        $orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);
        $methodInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->paymentMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($methodInstanceMock);
        $methodInstanceMock->expects($this->atLeastOnce())
            ->method('canRefund')
            ->willReturn(true);
        $this->invoiceMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn(1);
        $this->invoiceMock->expects($this->once())
            ->method('getBaseTotalRefunded')
            ->willReturn(0);
        $this->assertEquals(
            [],
            $this->validator->validate($this->invoiceMock)
        );
    }
}

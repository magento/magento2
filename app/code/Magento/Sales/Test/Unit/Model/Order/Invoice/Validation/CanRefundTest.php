<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Invoice\Validation;

use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Validation\CanRefund;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CanRefundTest extends TestCase
{
    /**
     * @var Invoice|MockObject
     */
    private $invoiceMock;

    /**
     * @var MockObject
     */
    private $orderPaymentRepositoryMock;

    /**
     * @var MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var MockObject
     */
    private $paymentMock;

    /**
     * @var CanRefund
     */
    private $validator;

    protected function setUp(): void
    {
        $this->invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderPaymentRepositoryMock = $this->getMockBuilder(
            OrderPaymentRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->paymentMock = $this->getMockBuilder(InfoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->validator = new CanRefund(
            $this->orderPaymentRepositoryMock,
            $this->orderRepositoryMock
        );
    }

    public function testValidateWrongInvoiceState()
    {
        $this->invoiceMock->expects($this->exactly(2))
            ->method('getState')
            ->willReturnOnConsecutiveCalls(
                Invoice::STATE_OPEN,
                Invoice::STATE_CANCELED
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
            ->willReturn(Invoice::STATE_PAID);
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
            ->willReturn(Invoice::STATE_PAID);
        $orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);
        $methodInstanceMock = $this->getMockBuilder(MethodInterface::class)
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

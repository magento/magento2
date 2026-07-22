<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Invoice\Validation;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                ScopeConfigInterface::class,
                $this->createMock(ScopeConfigInterface::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $this->invoiceMock = $this->createMock(Invoice::class);
        $this->orderPaymentRepositoryMock = $this->createMock(
            OrderPaymentRepositoryInterface::class
        );
        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->paymentMock = $this->createMock(InfoInterface::class);
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
        $orderMock = $this->createMock(OrderInterface::class);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);
        $methodInstanceMock = $this->createMock(MethodInterface::class);
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

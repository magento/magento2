<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice\PayOperation;
use Magento\Sales\Model\Order\PaymentAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for payment adapter.
 */
class PaymentAdapterTest extends TestCase
{

    /**
     * @var PaymentAdapter
     */
    private $subject;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    /**
     * @var CreditmemoInterface|MockObject
     */
    private $creditmemoMock;

    /**
     * @var InvoiceInterface|MockObject
     */
    private $invoiceMock;

    /**
     * @var PayOperation|MockObject
     */
    private $payOperationMock;

    protected function setUp(): void
    {
        $this->orderMock = $this->createMock(OrderInterface::class);

        $this->creditmemoMock = $this->createMock(CreditmemoInterface::class);

        $this->invoiceMock = $this->createMock(InvoiceInterface::class);

        $this->payOperationMock = $this->createMock(PayOperation::class);

        $this->subject = new PaymentAdapter(
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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->invoiceMock = $this->getMockBuilder(InvoiceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->payOperationMock = $this->getMockBuilder(PayOperation::class)
            ->disableOriginalConstructor()
            ->getMock();

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

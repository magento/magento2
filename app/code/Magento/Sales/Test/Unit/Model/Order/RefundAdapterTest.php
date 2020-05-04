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
use Magento\Sales\Model\Order\Creditmemo\RefundOperation;
use Magento\Sales\Model\Order\RefundAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for refund adapter.
 */
class RefundAdapterTest extends TestCase
{
    /**
     * @var RefundAdapter
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
     * @var RefundOperation|MockObject
     */
    private $refundOperationMock;

    /**
     * @var InvoiceInterface|MockObject
     */
    private $invoiceMock;

    protected function setUp(): void
    {
        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->refundOperationMock = $this->getMockBuilder(RefundOperation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceMock = $this->getMockBuilder(InvoiceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->subject = new RefundAdapter(
            $this->refundOperationMock
        );
    }

    public function testRefund()
    {
        $isOnline = true;
        $this->refundOperationMock->expects($this->once())
            ->method('execute')
            ->with($this->creditmemoMock, $this->orderMock, $isOnline)
            ->willReturn($this->orderMock);
        $this->assertEquals(
            $this->orderMock,
            $this->subject->refund($this->creditmemoMock, $this->orderMock, $isOnline)
        );
    }
}

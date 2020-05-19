<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Creditmemo\Relation;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Relation\Refund;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RefundTest extends TestCase
{
    /**
     * @var Refund
     */
    protected $refundResource;

    /**
     * @var MockObject
     */
    protected $orderRepositoryMock;

    /**
     * @var MockObject
     */
    protected $invoiceRepositoryMock;

    /**
     * @var MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->invoiceRepositoryMock = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->refundResource = $objectManager->getObject(
            Refund::class,
            [
                'orderRepository' => $this->orderRepositoryMock,
                'invoiceRepository' => $this->invoiceRepositoryMock,
                'priceCurrency' => $this->priceCurrencyMock
            ]
        );
    }

    public function testProcessRelation()
    {
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoMock->expects($this->once())
            ->method('getState')
            ->willReturn(Creditmemo::STATE_REFUNDED);
        $creditmemoMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($orderMock);

        $this->assertNull($this->refundResource->processRelation($creditmemoMock));
    }
}

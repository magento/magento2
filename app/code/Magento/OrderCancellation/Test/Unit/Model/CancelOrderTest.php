<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellation\Test\Unit\Model;

use Magento\Framework\Escaper;
use Magento\OrderCancellation\Model\CancelOrder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\RefundInvoice;
use Magento\Sales\Model\RefundOrder;
use Magento\Payment\Model\MethodInterface;
use Magento\Framework\Phrase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CancelOrderTest extends TestCase
{
    /** @var RefundInvoice|MockObject */
    private RefundInvoice $refundInvoice;

    /** @var RefundOrder|MockObject */
    private RefundOrder $refundOrder;

    /** @var OrderRepositoryInterface|MockObject */
    private OrderRepositoryInterface $orderRepository;

    /** @var Escaper|MockObject */
    private Escaper $escaper;

    /** @var OrderCommentSender|MockObject */
    private OrderCommentSender $sender;

    /**
     * @var CancelOrder
     */
    private CancelOrder $cancelOrder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->refundInvoice = $this->createMock(RefundInvoice::class);
        $this->refundOrder = $this->createMock(RefundOrder::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->escaper = $this->createMock(Escaper::class);
        $this->sender = $this->createMock(OrderCommentSender::class);

        $this->cancelOrder = new CancelOrder(
            $this->refundInvoice,
            $this->refundOrder,
            $this->orderRepository,
            $this->escaper,
            $this->sender
        );
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Sales\Exception\CouldNotRefundException
     * @throws \Magento\Sales\Exception\DocumentValidationException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testExecuteTriggersFreePaymentCheck(): void
    {
        $reason = '<b>Customer requested</b>';
        $escapedReason = 'Customer requested';
        $orderId = 42;

        $payment = $this->createMock(Payment::class);
        $payment->expects($this->once())->method('getAmountPaid')->willReturn(null);
        $payment->expects($this->once())->method('getMethod')->willReturn('free');

        $methodInstance = $this->createMock(MethodInterface::class);
        $methodInstance->method('isOffline')->willReturn(true);
        $payment->method('getMethodInstance')->willReturn($methodInstance);

        $order = $this->createMock(Order::class);
        $order->method('getPayment')->willReturn($payment);
        $order->method('getEntityId')->willReturn($orderId);

        $this->refundOrder
            ->expects(self::once())
            ->method('execute')
            ->with($orderId);

        $reloadedOrder = $this->createMock(Order::class);
        $reloadedOrder->method('cancel')->willReturnSelf();
        $reloadedOrder->method('getRealOrderId')->willReturn('000000123');
        $reloadedOrder->method('getStatus')->willReturn('canceled');
        $reloadedOrder->expects(self::exactly(2))->method('addCommentToStatusHistory');

        $this->orderRepository
            ->expects(self::once())
            ->method('get')
            ->with($orderId)
            ->willReturn($reloadedOrder);

        $this->sender
            ->expects(self::once())
            ->method('send')
            ->with(
                $reloadedOrder,
                true,
                self::callback(function ($phrase) {
                    return $phrase instanceof Phrase
                        && (string)$phrase === 'Order 000000123 was cancelled';
                })
            )
            ->willReturn(true);

        $this->escaper
            ->expects(self::once())
            ->method('escapeHtml')
            ->with($reason)
            ->willReturn($escapedReason);

        $this->orderRepository
            ->expects(self::once())
            ->method('save')
            ->with($reloadedOrder)
            ->willReturn($reloadedOrder);

        $result = $this->cancelOrder->execute($order, $reason);

        self::assertSame($reloadedOrder, $result);
    }
}

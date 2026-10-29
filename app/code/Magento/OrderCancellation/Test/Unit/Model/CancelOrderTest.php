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
    public function testExecuteSkipsRefundForFreePaymentMethod(): void
    {
        $reason = '<b>Customer requested</b>';
        $escapedReason = 'Customer requested';

        $payment = $this->createMock(Payment::class);
        $payment->expects($this->once())->method('getMethod')->willReturn('free');
        $payment->expects($this->never())->method('getAmountPaid');
        $payment->expects($this->never())->method('getMethodInstance');

        $order = $this->createMock(Order::class);
        $order->method('getPayment')->willReturn($payment);
        $order->expects(self::once())->method('cancel')->willReturnSelf();
        $order->method('getRealOrderId')->willReturn('000000123');
        $order->method('getStatus')->willReturn('canceled');
        $order->expects(self::exactly(2))->method('addCommentToStatusHistory');

        $this->refundOrder->expects(self::never())->method('execute');

        $this->orderRepository->expects(self::never())->method('get');

        $this->sender
            ->expects(self::once())
            ->method('send')
            ->with(
                $order,
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
            ->with($order)
            ->willReturn($order);

        $result = $this->cancelOrder->execute($order, $reason);

        self::assertSame($order, $result);
    }
}

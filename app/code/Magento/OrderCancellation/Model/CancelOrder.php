<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellation\Model;

use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Exception\CouldNotRefundException;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Model\RefundInvoice;
use Magento\Sales\Model\RefundOrder;

/**
 * To cancel an order including online or offline payment refund and updates status accordingly.
 */
class CancelOrder
{
    private const EMAIL_NOTIFICATION_SUCCESS = "Order cancellation notification email was sent.";
    private const EMAIL_NOTIFICATION_ERROR = "Email notification failed.";

    /**
     * CancelOrder constructor
     *
     * @param RefundInvoice $refundInvoice
     * @param RefundOrder $refundOrder
     * @param OrderRepositoryInterface $orderRepository
     * @param Escaper $escaper
     * @param OrderCommentSender $sender
     */
    public function __construct(
        private readonly RefundInvoice $refundInvoice,
        private readonly RefundOrder $refundOrder,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly Escaper $escaper,
        private readonly OrderCommentSender $sender
    ) {
    }

    /**
     * To cancel an order and if applicable process a refund
     *
     * @param Order $order
     * @param string $reason
     * @return Order
     * @throws LocalizedException
     * @throws CouldNotRefundException
     * @throws DocumentValidationException
     */
    public function execute(
        Order $order,
        string $reason
    ): Order {
        $payment = $order->getPayment();

        if ($payment->getAmountPaid() !== null) {
            $order = $payment->getMethodInstance()->isOffline()
                ? $this->handleOfflinePayment($order)
                : $this->handleOnlinePayment($order);
        } else {
            $order->cancel();
        }

        return $this->updateOrderComments($order, $reason);
    }

    /**
     * Update order comments
     *
     * @param OrderInterface $order
     * @param string $reason
     * @return OrderInterface
     */
    public function updateOrderComments(OrderInterface $order, string $reason): OrderInterface
    {
        $result = $this->sender->send($order, true, __("Order %1 was cancelled", $order->getRealOrderId()));

        $order->addCommentToStatusHistory(
            __("%1", $result ? self::EMAIL_NOTIFICATION_SUCCESS : self::EMAIL_NOTIFICATION_ERROR),
            $order->getStatus(),
            true
        );

        $order->addCommentToStatusHistory($this->escaper->escapeHtml($reason), $order->getStatus(), true);

        return $this->orderRepository->save($order);
    }

    /**
     *  Handle order with offline payment
     *
     * @param OrderInterface $order
     * @return OrderInterface
     */
    private function handleOfflinePayment(OrderInterface $order): OrderInterface
    {
        $this->refundOrder->execute($order->getEntityId());
        return $this->orderRepository->get($order->getEntityId())->cancel();
    }

    /**
     * Handle order with online payment
     *
     * @param OrderInterface $order
     * @return OrderInterface
     */
    private function handleOnlinePayment(OrderInterface $order): OrderInterface
    {
        foreach ($order->getInvoiceCollection() as $invoice) {
            $this->refundInvoice->execute($invoice->getEntityId());
        }
        return $this->orderRepository->get($order->getEntityId());
    }
}

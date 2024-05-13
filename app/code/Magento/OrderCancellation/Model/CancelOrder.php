<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OrderCancellation\Model;

use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Exception\CouldNotRefundException;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\RefundInvoice;
use Magento\Sales\Model\RefundOrder;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;

/**
 * Cancels an order including online or offline payment refund and updates status accordingly.
 */
class CancelOrder
{
    private const EMAIL_NOTIFICATION_SUCCESS = "Order cancellation notification email was sent.";

    private const EMAIL_NOTIFICATION_ERROR = "Email notification failed.";

    /**
     * @var OrderCommentSender
     */
    private OrderCommentSender $sender;

    /**
     * @var RefundInvoice
     */
    private RefundInvoice $refundInvoice;

    /**
     * @var RefundOrder
     */
    private RefundOrder $refundOrder;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @param RefundInvoice $refundInvoice
     * @param RefundOrder $refundOrder
     * @param OrderRepositoryInterface $orderRepository
     * @param Escaper $escaper
     * @param OrderCommentSender $sender
     */
    public function __construct(
        RefundInvoice $refundInvoice,
        RefundOrder $refundOrder,
        OrderRepositoryInterface $orderRepository,
        Escaper $escaper,
        OrderCommentSender $sender
    ) {
        $this->refundInvoice = $refundInvoice;
        $this->refundOrder = $refundOrder;
        $this->orderRepository = $orderRepository;
        $this->escaper = $escaper;
        $this->sender = $sender;
    }

    /**
     * Cancels and refund an order, if applicable.
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
        /** @var OrderPaymentInterface $payment */
        $payment = $order->getPayment();
        if ($payment->getAmountPaid() === null) {
            $order->cancel();
        } else {
            if ($payment->getMethodInstance()->isOffline()) {
                $this->refundOrder->execute($order->getEntityId());
                // for partially invoiced orders we need to cancel after doing the refund
                // so not invoiced items are cancelled and the whole order is set to cancelled
                $order = $this->orderRepository->get($order->getId());
                $order->cancel();
            } else {
                /** @var Order\Invoice $invoice */
                foreach ($order->getInvoiceCollection() as $invoice) {
                    $this->refundInvoice->execute($invoice->getEntityId());
                }
                // in this case order needs to be re-instantiated
                $order = $this->orderRepository->get($order->getId());
            }
        }

        $result = $this->sender->send(
            $order,
            true,
            __("Order %1 was cancelled", $order->getRealOrderId())
        );
        $order->addCommentToStatusHistory(
            $result ?
                __("%1", CancelOrder::EMAIL_NOTIFICATION_SUCCESS) : __("%1", CancelOrder::EMAIL_NOTIFICATION_ERROR)
        );

        $order->addCommentToStatusHistory(
            $this->escaper->escapeHtml($reason),
            $order->getStatus()
        );

        return $this->orderRepository->save($order);
    }
}

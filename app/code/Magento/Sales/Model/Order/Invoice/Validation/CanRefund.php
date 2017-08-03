<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice\Validation;

use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Class CanRefund
 * @since 2.2.0
 */
class CanRefund implements ValidatorInterface
{
    /**
     * @var OrderPaymentRepositoryInterface
     * @since 2.2.0
     */
    private $paymentRepository;

    /**
     * @var OrderRepositoryInterface
     * @since 2.2.0
     */
    private $orderRepository;

    /**
     * CanRefund constructor.
     *
     * @param OrderPaymentRepositoryInterface $paymentRepository
     * @param OrderRepositoryInterface $orderRepository
     * @since 2.2.0
     */
    public function __construct(
        OrderPaymentRepositoryInterface $paymentRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function validate($entity)
    {
        if ($entity->getState() == Invoice::STATE_PAID &&
            $this->isGrandTotalEnoughToRefund($entity) &&
            $this->isPaymentAllowRefund($entity)
        ) {
            return [];
        }

        return [__('We can\'t create creditmemo for the invoice.')];
    }

    /**
     * @param InvoiceInterface $invoice
     * @return bool
     * @since 2.2.0
     */
    private function isPaymentAllowRefund(InvoiceInterface $invoice)
    {
        $order = $this->orderRepository->get($invoice->getOrderId());
        $payment = $order->getPayment();
        if (!$payment instanceof InfoInterface) {
            return false;
        }
        $method = $payment->getMethodInstance();
        return $this->canPartialRefund($method, $payment) || $this->canFullRefund($invoice, $method);
    }

    /**
     * @param InvoiceInterface $entity
     * @return bool
     * @since 2.2.0
     */
    private function isGrandTotalEnoughToRefund(InvoiceInterface $entity)
    {
        return abs($entity->getBaseGrandTotal() - $entity->getBaseTotalRefunded()) >= .0001;
    }

    /**
     * @param MethodInterface $method
     * @param InfoInterface $payment
     * @return bool
     * @since 2.2.0
     */
    private function canPartialRefund(MethodInterface $method, InfoInterface $payment)
    {
        return $method->canRefund() &&
        $method->canRefundPartialPerInvoice() &&
        $payment->getAmountPaid() > $payment->getAmountRefunded();
    }

    /**
     * @param InvoiceInterface $invoice
     * @param MethodInterface $method
     * @return bool
     * @since 2.2.0
     */
    private function canFullRefund(InvoiceInterface $invoice, MethodInterface $method)
    {
        return $method->canRefund() && !$invoice->getIsUsedForRefund();
    }
}

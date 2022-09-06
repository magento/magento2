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
use Magento\Framework\App\ObjectManager;

class CanRefund implements ValidatorInterface
{
    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * CanRefund constructor.
     *
     * @param OrderPaymentRepositoryInterface $paymentRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OrderPaymentRepositoryInterface $paymentRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->objectManager = ObjectManager::getInstance();
        $this->paymentRepository = $paymentRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
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
     * Validate if a refund is possible for the payment method
     *
     * @param InvoiceInterface $invoice
     * @return bool
     */
    private function isPaymentAllowRefund(InvoiceInterface $invoice)
    {
        $order = $this->orderRepository->get($invoice->getOrderId());
        $payment = $order->getPayment();
        if (!$payment instanceof InfoInterface) {
            return false;
        }
        $method = $payment->getMethodInstance();
        if (!$method instanceof \Magento\Payment\Model\Method\Free) {
            return $this->canPartialRefund($method, $payment) || $this->canFullRefund($invoice, $method);
        }
        return true;
    }

    /**
     * Validate if available grand total is enough to be refunded
     *
     * @param InvoiceInterface $entity
     * @return bool
     */
    private function isGrandTotalEnoughToRefund(InvoiceInterface $entity)
    {
        $isAllowedZeroGrandTotal = $this->objectManager->get(ScopeConfigInterface::class)->getValue(
            'sales/zerograndtotal_creditmemo/allow_zero_grandtotal',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return abs($entity->getBaseGrandTotal() - $entity->getBaseTotalRefunded()) >= .0001 ||
            $isAllowedZeroGrandTotal;
    }

    /**
     * Validate if partial refund is possible
     *
     * @param MethodInterface $method
     * @param InfoInterface $payment
     * @return bool
     */
    private function canPartialRefund(MethodInterface $method, InfoInterface $payment)
    {
        return $method->canRefund() &&
        $method->canRefundPartialPerInvoice() &&
        $payment->getAmountPaid() > $payment->getAmountRefunded();
    }

    /**
     * Validate if full refund is possible
     *
     * @param InvoiceInterface $invoice
     * @param MethodInterface $method
     * @return bool
     */
    private function canFullRefund(InvoiceInterface $invoice, MethodInterface $method)
    {
        return $method->canRefund() && !$invoice->getIsUsedForRefund();
    }
}

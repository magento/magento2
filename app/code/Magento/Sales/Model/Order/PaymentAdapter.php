<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

/**
 * Payment adapter.
 *
 * @api
 */
class PaymentAdapter implements PaymentAdapterInterface
{
    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\RefundOperation
     */
    private $refundOperation;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\PayOperation
     */
    private $payOperation;

    /**
     * PaymentAdapter constructor.
     * @param \Magento\Sales\Model\Order\Creditmemo\RefundOperation $refundOperation
     * @param \Magento\Sales\Model\Order\Invoice\PayOperation $payOperation
     */
    public function __construct(
        \Magento\Sales\Model\Order\Creditmemo\RefundOperation $refundOperation,
        \Magento\Sales\Model\Order\Invoice\PayOperation $payOperation
    ) {
        $this->refundOperation = $refundOperation;
        $this->payOperation = $payOperation;
    }

    /**
     * {@inheritdoc}
     */
    public function refund(
        \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo,
        \Magento\Sales\Api\Data\OrderInterface $order,
        $isOnline = false
    ) {
        return $this->refundOperation->execute($creditmemo, $order, $isOnline);
    }

    /**
     * {@inheritdoc}
     */
    public function pay(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\Data\InvoiceInterface $invoice,
        $capture
    ) {
        return $this->payOperation->execute($order, $invoice, $capture);
    }
}

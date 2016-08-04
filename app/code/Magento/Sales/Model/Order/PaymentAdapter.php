<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @var \Magento\Sales\Model\Order\Invoice\PayOperation
     */
    private $payOperation;

    /**
     * @param \Magento\Sales\Model\Order\Invoice\PayOperation $payOperation
     */
    public function __construct(
        \Magento\Sales\Model\Order\Invoice\PayOperation $payOperation
    ) {
        $this->payOperation = $payOperation;
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

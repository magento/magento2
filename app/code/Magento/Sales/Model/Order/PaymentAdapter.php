<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice\PayOperation;

/**
 * @inheritdoc
 */
class PaymentAdapter implements PaymentAdapterInterface
{
    /**
     * @var PayOperation
     */
    private $payOperation;

    /**
     * @param PayOperation $payOperation
     */
    public function __construct(
        PayOperation $payOperation
    ) {
        $this->payOperation = $payOperation;
    }

    /**
     * @inheritdoc
     */
    public function pay(
        OrderInterface $order,
        InvoiceInterface $invoice,
        $capture
    ) {
        return $this->payOperation->execute($order, $invoice, $capture);
    }
}

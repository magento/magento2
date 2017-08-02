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
 * @since 2.1.2
 */
class PaymentAdapter implements PaymentAdapterInterface
{
    /**
     * @var PayOperation
     * @since 2.1.2
     */
    private $payOperation;

    /**
     * @param PayOperation $payOperation
     * @since 2.1.2
     */
    public function __construct(
        PayOperation $payOperation
    ) {
        $this->payOperation = $payOperation;
    }

    /**
     * @inheritdoc
     * @since 2.1.2
     */
    public function pay(
        OrderInterface $order,
        InvoiceInterface $invoice,
        $capture
    ) {
        return $this->payOperation->execute($order, $invoice, $capture);
    }
}

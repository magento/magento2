<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface OrderStatisticInterface
 *
 * @api
 * @since 100.1.2
 */
interface InvoiceStatisticInterface
{
    /**
     * @param OrderInterface $order
     * @param InvoiceInterface $invoice
     * @return OrderInterface
     * @since 100.1.2
     */
    public function register(OrderInterface $order, InvoiceInterface $invoice);
}

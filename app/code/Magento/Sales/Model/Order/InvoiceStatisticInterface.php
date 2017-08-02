<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface OrderStatisticInterface
 *
 * @api
 * @since 2.2.0
 */
interface InvoiceStatisticInterface
{
    /**
     * @param OrderInterface $order
     * @param InvoiceInterface $invoice
     * @return OrderInterface
     * @since 2.2.0
     */
    public function register(OrderInterface $order, InvoiceInterface $invoice);
}

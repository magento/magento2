<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface PaymentAdapterInterface
 *
 * @api
 */
interface PaymentAdapterInterface
{
    /**
     * @param OrderInterface $order
     * @param InvoiceInterface $invoice
     * @param bool $capture
     * @return OrderInterface
     */
    public function pay(OrderInterface $order, InvoiceInterface $invoice, $capture);
}

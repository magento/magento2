<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Encapsulates payment operation behind unified interface.
 * Can be used as extension point.
 *
 * @api
 * @since 2.2.0
 */
interface PaymentAdapterInterface
{
    /**
     * @param OrderInterface $order
     * @param InvoiceInterface $invoice
     * @param bool $capture
     * @return OrderInterface
     * @since 2.2.0
     */
    public function pay(OrderInterface $order, InvoiceInterface $invoice, $capture);
}

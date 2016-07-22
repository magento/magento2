<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface InvoiceValidatorInterface
 *
 * @api
 */
interface InvoiceValidatorInterface
{
    /**
     * @param InvoiceInterface $invoice
     * @param OrderInterface $order
     * @return array
     */
    public function validate(InvoiceInterface $invoice, OrderInterface $order);
}

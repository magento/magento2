<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceCommentBaseInterface;

/**
 * Interface InvoiceNotifierInterface
 *
 * @api
 */
interface InvoiceNotifierInterface
{
    /**
     * @param OrderInterface $order
     * @param InvoiceInterface $invoice
     * @param InvoiceCommentBaseInterface $comment
     * @return void
     */
    public function notify(
        OrderInterface $order,
        InvoiceInterface $invoice,
        InvoiceCommentBaseInterface $comment = null
    );
}

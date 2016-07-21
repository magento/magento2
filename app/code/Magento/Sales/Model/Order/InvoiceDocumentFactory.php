<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\InvoiceInterface;

/**
 * Class InvoiceDocumentFactory
 *
 * @api
 */
class InvoiceDocumentFactory
{
    /**
     * @param OrderInterface $order
     * @param InvoiceItemCreationInterface[] $items
     * @param InvoiceCommentCreationInterface|null $comment
     * @param InvoiceCreationArgumentsInterface|null $arguments
     * @return InvoiceInterface
     */
    public function create(
        $order,
        $items = [],
        InvoiceCommentCreationInterface $comment = null,
        InvoiceCreationArgumentsInterface $arguments = null
    ) {
        return null;
    }
}
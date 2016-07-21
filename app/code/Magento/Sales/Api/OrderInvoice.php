<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Api;

/**
 * Class OrderInvoiceInterface
 *  Should be part of OrderManagementInterface::invoice()
 *
 * @api
 */
interface OrderInvoiceInterface
{
    /**
     * @param int $orderId
     * @param bool|false $capture
     * @param \Magento\Sales\Api\Data\InvoiceItemCreationInterface[] $items
     * @param bool|false $notify
     * @param Data\InvoiceCommentCreationInterface|null $comment
     * @param Data\InvoiceCreationArgumentsInterface|null $arguments
     * @return int
     */
    public function execute(
        $orderId,
        $capture = false,
        array $items = [],
        $notify = false,
        \Magento\Sales\Api\Data\InvoiceCommentCreationInterface $comment = null,
        \Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface $arguments = null
    );
}

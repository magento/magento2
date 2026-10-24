<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sales\Api;

/**
 * Class InvoiceOrderInterface
 *
 * @api
 * @since 100.1.2
 */
interface InvoiceOrderInterface
{
    /**
     * @param int $orderId
     * @param bool|false $capture
     * @param \Magento\Sales\Api\Data\InvoiceItemCreationInterface[] $items
     * @param bool|false $notify
     * @param bool|false $appendComment
     * @param Data\InvoiceCommentCreationInterface|null $comment
     * @param Data\InvoiceCreationArgumentsInterface|null $arguments
     * @return int
     * @since 100.1.2
     */
    public function execute(
        $orderId,
        $capture = false,
        array $items = [],
        $notify = false,
        $appendComment = false,
        ?\Magento\Sales\Api\Data\InvoiceCommentCreationInterface $comment = null,
        ?\Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface $arguments = null
    );
}

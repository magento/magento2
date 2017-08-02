<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Api;

/**
 * Class InvoiceOrderInterface
 *
 * @api
 * @since 2.2.0
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
     * @since 2.2.0
     */
    public function execute(
        $orderId,
        $capture = false,
        array $items = [],
        $notify = false,
        $appendComment = false,
        \Magento\Sales\Api\Data\InvoiceCommentCreationInterface $comment = null,
        \Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface $arguments = null
    );
}

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Api;

/**
 * Class InvoiceCaptureInterface
 *
 * @api
 */
interface InvoiceCaptureInterface
{
    /**
     * @param int $orderId
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface[] $items
     * @param bool|false $notify
     * @param Data\InvoiceCommentBaseInterface|null $comment
     * @param Data\InvoiceCreationArgumentsInterface|null $arguments
     * @return int
     */
    public function captureOffline(
        $orderId,
        array $items = [],
        $notify = false,
        \Magento\Sales\Api\Data\InvoiceCommentBaseInterface $comment = null,
        \Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface $arguments = null
    );

    /**
     * @param int $orderId
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface[] $items
     * @param bool|false $notify
     * @param Data\InvoiceCommentBaseInterface|null $comment
     * @param Data\InvoiceCreationArgumentsInterface|null $arguments
     * @return int
     */
    public function captureOnline(
        $orderId,
        array $items = [],
        $notify = false,
        \Magento\Sales\Api\Data\InvoiceCommentBaseInterface $comment = null,
        \Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface $arguments = null
    );
}

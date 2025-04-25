<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Interface RefundInvoiceInterface
 *
 * @api
 * @since 100.1.3
 */
interface RefundInvoiceInterface
{
    /**
     * Create refund for invoice
     *
     * @param int $invoiceId
     * @param \Magento\Sales\Api\Data\CreditmemoItemCreationInterface[] $items
     * @param bool|null $isOnline
     * @param bool|null $notify
     * @param bool|null $appendComment
     * @param \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface|null $comment
     * @param \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface|null $arguments
     * @return int
     * @since 100.1.3
     */
    public function execute(
        $invoiceId,
        array $items = [],
        $isOnline = false,
        $notify = false,
        $appendComment = false,
        ?\Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        ?\Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    );
}

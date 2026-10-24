<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Api;

/**
 * Interface RefundOrderInterface
 *
 * @api
 * @since 100.1.3
 */
interface RefundOrderInterface
{
    /**
     * Create offline refund for order
     *
     * @param int $orderId
     * @param \Magento\Sales\Api\Data\CreditmemoItemCreationInterface[] $items
     * @param bool|null $notify
     * @param bool|null $appendComment
     * @param \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface|null $comment
     * @param \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface|null $arguments
     * @return int
     * @since 100.1.3
     */
    public function execute(
        $orderId,
        array $items = [],
        $notify = false,
        $appendComment = false,
        ?\Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        ?\Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    );
}

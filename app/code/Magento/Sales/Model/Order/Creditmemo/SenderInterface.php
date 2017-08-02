<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

/**
 * Interface for notification sender for CreditMemo.
 * @since 2.2.0
 */
interface SenderInterface
{
    /**
     * Sends notification to a customer.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     * @param \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface|null $comment
     * @param bool $forceSyncMode
     *
     * @return bool
     * @since 2.2.0
     */
    public function send(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo,
        \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        $forceSyncMode = false
    );
}

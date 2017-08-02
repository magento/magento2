<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

/**
 * Interface for CreditMemo notifier.
 *
 * @api
 * @since 2.2.0
 */
interface NotifierInterface
{
    /**
     * Notifies customer.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     * @param \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface|null $comment
     * @param bool $forceSyncMode
     *
     * @return void
     * @since 2.2.0
     */
    public function notify(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo,
        \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        $forceSyncMode = false
    );
}

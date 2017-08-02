<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

/**
 * CreditMemo notifier.
 *
 * @api
 * @since 2.2.0
 */
class Notifier implements \Magento\Sales\Model\Order\Creditmemo\NotifierInterface
{
    /**
     * @var \Magento\Sales\Model\Order\CreditMemo\SenderInterface[]
     * @since 2.2.0
     */
    private $senders;

    /**
     * @param \Magento\Sales\Model\Order\CreditMemo\SenderInterface[] $senders
     * @since 2.2.0
     */
    public function __construct(array $senders = [])
    {
        $this->senders = $senders;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function notify(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo,
        \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        $forceSyncMode = false
    ) {
        foreach ($this->senders as $sender) {
            $sender->send($order, $creditmemo, $comment, $forceSyncMode);
        }
    }
}

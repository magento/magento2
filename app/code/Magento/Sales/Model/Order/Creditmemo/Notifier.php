<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

/**
 * CreditMemo notifier.
 */
class Notifier implements \Magento\Sales\Model\Order\Creditmemo\NotifierInterface
{
    /**
     * @var \Magento\Sales\Model\Order\CreditMemo\SenderInterface[]
     */
    private $senders;

    /**
     * @param \Magento\Sales\Model\Order\CreditMemo\SenderInterface[] $senders
     */
    public function __construct(array $senders = [])
    {
        $this->senders = $senders;
    }

    /**
     * {@inheritdoc}
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

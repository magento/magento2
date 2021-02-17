<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model\Plugin;

use Magento\Newsletter\Model\ResourceModel\Queue as QueueResource;
use Magento\Newsletter\Model\Subscriber;

/**
 * Plugin responsible for removing subscriber from queue after unsubscribe
 */
class RemoveSubscriberFromQueue
{
    private const STATUS = 'subscriber_status';

    /**
     * @var QueueResource
     */
    private $queueResource;

    /**
     * @param QueueResource $queueResource
     */
    public function __construct(QueueResource $queueResource)
    {
        $this->queueResource = $queueResource;
    }

    /**
     * Removes subscriber from queue
     *
     * @param Subscriber $subject
     * @param Subscriber $subscriber
     * @return Subscriber
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUnsubscribe(Subscriber $subject, Subscriber $subscriber): Subscriber
    {
        if ($subscriber->isStatusChanged() && $subscriber->getSubscriberStatus() === Subscriber::STATUS_UNSUBSCRIBED) {
            $this->queueResource->removeSubscriberFromQueue((int) $subscriber->getId());
        }

        return $subscriber;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model\Plugin;

use Magento\Newsletter\Model\RemoveSubscriberFromQueueLink;
use Magento\Newsletter\Model\Subscriber;

/**
 * Plugin for removing subscriber from queue after unsubscribe
 */
class RemoveSubscriberFromQueue
{
    /**
     * @var RemoveSubscriberFromQueueLink
     */
    private $removeSubscriberFromQueue;

    /**
     * @param RemoveSubscriberFromQueueLink $removeSubscriberFromQueue
     */
    public function __construct(RemoveSubscriberFromQueueLink $removeSubscriberFromQueue)
    {
        $this->removeSubscriberFromQueue = $removeSubscriberFromQueue;
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
            $this->removeSubscriberFromQueue->execute((int) $subscriber->getId());
        }

        return $subscriber;
    }
}

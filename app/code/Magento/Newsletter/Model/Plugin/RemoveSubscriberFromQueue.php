<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model\Plugin;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Newsletter\Model\Subscriber;

/**
 * Plugin responsible for removing subscriber from queue after unsubscribe
 */
class RemoveSubscriberFromQueue
{
    private const STATUS = 'subscriber_status';

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
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
        if ($subscriber->dataHasChangedFor(self::STATUS)
            && $subscriber->getSubscriberStatus() === Subscriber::STATUS_UNSUBSCRIBED
        ) {
            $this->connection->delete(
                $this->connection->getTableName('newsletter_queue_link'),
                ['subscriber_id = ?' => $subscriber->getId(), 'letter_sent_at IS NULL']
            );
        }

        return $subscriber;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Responsible for removing subscriber from queue
 */
class RemoveSubscriberFromQueueLink
{
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
     * @param int $subscriberId
     * @return void
     */
    public function execute(int $subscriberId): void
    {
        $this->connection->delete(
            $this->connection->getTableName('newsletter_queue_link'),
            ['subscriber_id = ?' => $subscriberId, 'letter_sent_at IS NULL']
        );
    }
}

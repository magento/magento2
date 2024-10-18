<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Responsible for removing subscriber from queue
 */
class RemoveSubscriberFromQueueLink
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Removes subscriber from queue
     *
     * @param int $subscriberId
     * @return void
     */
    public function execute(int $subscriberId): void
    {
        $connection = $this->resourceConnection->getConnection();

        $connection->delete(
            $this->resourceConnection->getTableName('newsletter_queue_link'),
            ['subscriber_id = ?' => $subscriberId, 'letter_sent_at IS NULL']
        );
    }
}

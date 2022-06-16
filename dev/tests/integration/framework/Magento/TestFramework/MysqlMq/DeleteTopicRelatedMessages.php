<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\MysqlMq;

use Magento\MysqlMq\Model\QueueManagement;
use Magento\MysqlMq\Model\ResourceModel\Message;

/**
 * Delete messages from queue by topic
 */
class DeleteTopicRelatedMessages
{
    /** @var Message */
    private $queueMessageResource;

    /**
     * @param Message $queueMessageResource
     */
    public function __construct(
        Message $queueMessageResource
    ) {
        $this->queueMessageResource = $queueMessageResource;
    }

    /**
     * Delete messages from queue
     *
     * @param string $topic
     * @return void
     */
    public function execute(string $topic): void
    {
        $connection = $this->queueMessageResource->getConnection();
        $condition = $connection->quoteInto(QueueManagement::MESSAGE_TOPIC . '= ?', $topic);
        $connection->delete($this->queueMessageResource->getMainTable(), $condition);
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Resource;

use Magento\Framework\Amqp\MessageInterface;

/**
 * Resource model for queue message.
 */
class Message extends \Magento\Framework\Model\Resource\Db\AbstractDb implements MessageInterface
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('queue_message', 'id');
    }

    protected function getQueueMessageStatusTable()
    {
        return 'queue_message_status';
    }

    /**
     * {@inheritdoc}
     */
    public function linkQueues($queueNames)
    {
        $connection = $this->getConnection();
        foreach ($queueNames as $queueName) {
            // TODO: get ID of queue by name
            $queueId = $this->queueFactory->getQueueId($queueName);
            $connection->insert(
                $this->getQueueMessageStatusTable(),
                [
                    'queue_id' => $queueId,
                    'message_id' => $this->getId(),
                    // TODO: Verify that updated_at is set automatically on save
                    'status' => \Magento\MysqlMq\Model\Message::STATUS_NEW,
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages($queueName)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['queue_message' => $this->getMainTable()],
                ['topic_name', 'body'])
            ->joinLeft(
                ['queue_message_status' => $this->getQueueMessageStatusTable()],
                'queue_message.id = queue_message_status.message_id',
                [])
            ->joinLeft(
                ['queue' => 'queue'],
                'queue.id = queue_message_status.queue_id',
                [])
            ->where('queue.name = ?', $queueName);
        $query = $connection->query($select);
        return $query->fetchAll();
    }
}

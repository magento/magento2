<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Resource;

use Magento\MysqlMq\Model\QueueManagement;

/**
 * Resource model for queue.
 */
class Queue extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('queue', 'id');
    }

    /**
     * Save message to 'queue_message' table.
     *
     * @param string $messageTopic
     * @param string $messageBody
     * @return int ID of the inserted record
     */
    public function saveMessage($messageTopic, $messageBody)
    {
        $this->getConnection()->insert(
            $this->getMessageTable(),
            ['topic_name' => $messageTopic, 'body' => $messageBody]
        );
        return $this->getConnection()->lastInsertId($this->getMessageTable());
    }

    /**
     * Add associations between the specified message and queues.
     *
     * @param int $messageId
     * @param string[] $queueNames
     * @return $this
     */
    public function linkQueues($messageId, $queueNames)
    {
        $connection = $this->getConnection();
        $queueIds = $this->getQueueIdsByNames($queueNames);
        $data = [];
        foreach ($queueIds as $queueId) {
            $data[] = [
                $queueId,
                $messageId,
                QueueManagement::MESSAGE_STATUS_NEW
            ];
        }
        if (!empty($data)) {
            $connection->insertArray(
                $this->getMessageStatusTable(),
                ['queue_id', 'message_id', 'status'],
                $data
            );
        }
        return $this;
    }

    /**
     * Retrieve array of queue IDs corresponding to the specified array of queue names.
     *
     * @param string[] $queueNames
     * @return int[]
     */
    protected function getQueueIdsByNames($queueNames)
    {
        $selectObject = $this->getConnection()->select();
        $selectObject->from(['queue' => $this->getQueueTable()])
            ->columns(['id'])
            ->where('queue.name IN (?)', $queueNames);
        return $this->getConnection()->fetchCol($selectObject);
    }

    /**
     * Retrieve messages from the specified queue.
     *
     * @param string $queueName
     * @param int $limit
     * @return array
     */
    public function getMessages($queueName, $limit)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['queue_message' => $this->getMessageTable()],
                [QueueManagement::MESSAGE_TOPIC => 'topic_name', QueueManagement::MESSAGE_BODY => 'body']
            )->join(
                ['queue_message_status' => $this->getMessageStatusTable()],
                'queue_message.id = queue_message_status.message_id',
                [
                    QueueManagement::MESSAGE_QUEUE_RELATION_ID => 'id',
                    QueueManagement::MESSAGE_QUEUE_ID => 'queue_id',
                    QueueManagement::MESSAGE_ID => 'message_id',
                    QueueManagement::MESSAGE_STATUS => 'status',
                    QueueManagement::MESSAGE_UPDATED_AT => 'updated_at'
                ]
            )->join(
                ['queue' => $this->getQueueTable()],
                'queue.id = queue_message_status.queue_id',
                [QueueManagement::MESSAGE_QUEUE_NAME => 'name']
            )->where('queue.name = ?', $queueName)
            ->where('queue_message_status.status = ?', QueueManagement::MESSAGE_STATUS_NEW)
            ->order('queue_message_status.updated_at DESC')
            ->limit($limit);
        return $connection->fetchAll($select);
    }

    /**
     * Mark specified messages with 'in progress' status.
     *
     * @param int[] $relationIds
     * @return int[] IDs of messages which should be taken in progress by current process.
     */
    public function takeMessagesInProgress($relationIds)
    {
        $takenMessagesRelationIds = [];
        foreach ($relationIds as $relationId) {
            $affectedRows = $this->getConnection()->update(
                $this->getMessageStatusTable(),
                ['status' => QueueManagement::MESSAGE_STATUS_IN_PROGRESS],
                ['id = ?' => $relationId]
            );
            if ($affectedRows) {
                /**
                 * If status was set to 'in progress' by some other process (due to race conditions),
                 * current process should not process the same message.
                 * So message will be processed only if current process was able to change its status.
                 */
                $takenMessagesRelationIds[] = $relationId;
            }
        }
        return $takenMessagesRelationIds;
    }

    /**
     * Get name of table storing message statuses and associations to queues.
     *
     * @return string
     */
    protected function getMessageStatusTable()
    {
        return $this->getConnection()->getTableName('queue_message_status');
    }

    /**
     * Get name of table storing declared queues.
     *
     * @return string
     */
    protected function getQueueTable()
    {
        return $this->getConnection()->getTableName('queue');
    }

    /**
     * Get name of table storing message body and topic.
     *
     * @return string
     */
    protected function getMessageTable()
    {
        return $this->getConnection()->getTableName('queue_message');
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\ResourceModel;

use Magento\MysqlMq\Model\QueueManagement;

/**
 * Resource model for queue.
 */
class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
     * @param int|null $limit
     * @return array
     */
    public function getMessages($queueName, $limit = null)
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
                    QueueManagement::MESSAGE_UPDATED_AT => 'updated_at',
                    QueueManagement::MESSAGE_NUMBER_OF_TRIALS => 'number_of_trials'
                ]
            )->join(
                ['queue' => $this->getQueueTable()],
                'queue.id = queue_message_status.queue_id',
                [QueueManagement::MESSAGE_QUEUE_NAME => 'name']
            )->where(
                'queue_message_status.status IN (?)',
                [QueueManagement::MESSAGE_STATUS_NEW, QueueManagement::MESSAGE_STATUS_RETRY_REQUIRED]
            )->where('queue.name = ?', $queueName)
            ->order('queue_message_status.updated_at DESC');

        if ($limit) {
            $select->limit($limit);
        }

        return $connection->fetchAll($select);
    }

    /**
     * Delete messages if there is no queue whrere the message is not in status TO BE DELETED
     *
     * @return void
     */
    public function deleteMarkedMessages()
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from(['queue_message_status' => $this->getMessageStatusTable()], ['message_id'])
            ->where('status <> ?', QueueManagement::MESSAGE_STATUS_TO_BE_DELETED)
            ->distinct();
        $messageIds = $connection->fetchCol($select);

        $condition = count($messageIds) > 0 ? ['id NOT IN (?)' => $messageIds] : null;
        $connection->delete($this->getMessageTable(), $condition);
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
     * Set status of message to 'retry required' and increment number of processing trials.
     *
     * @param int $relationId
     * @return void
     */
    public function pushBackForRetry($relationId)
    {
        $this->getConnection()->update(
            $this->getMessageStatusTable(),
            [
                'status' => QueueManagement::MESSAGE_STATUS_RETRY_REQUIRED,
                'number_of_trials' => new \Zend_Db_Expr('number_of_trials+1')
            ],
            ['id = ?' => $relationId]
        );
    }

    /**
     * Change message status.
     *
     * @param int[] $relationIds
     * @param int $status
     * @return void
     */
    public function changeStatus($relationIds, $status)
    {
        $this->getConnection()->update(
            $this->getMessageStatusTable(),
            ['status' => $status],
            ['id IN (?)' => $relationIds]
        );
    }

    /**
     * Get name of table storing message statuses and associations to queues.
     *
     * @return string
     */
    protected function getMessageStatusTable()
    {
        return $this->getTable('queue_message_status');
    }

    /**
     * Get name of table storing declared queues.
     *
     * @return string
     */
    protected function getQueueTable()
    {
        return $this->getTable('queue');
    }

    /**
     * Get name of table storing message body and topic.
     *
     * @return string
     */
    protected function getMessageTable()
    {
        return $this->getTable('queue_message');
    }
}

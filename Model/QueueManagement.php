<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

/**
 * Main class for managing MySQL implementation of message queue.
 */
class QueueManagement
{
    const MESSAGE_TOPIC = 'topic_name';
    const MESSAGE_BODY = 'body';
    const MESSAGE_ID = 'message_id';
    const MESSAGE_STATUS = 'status';
    const MESSAGE_UPDATED_AT = 'updated_at';
    const MESSAGE_QUEUE_ID = 'queue_id';
    const MESSAGE_QUEUE_NAME = 'queue_name';
    const MESSAGE_QUEUE_RELATION_ID = 'relation_id';
    const MESSAGE_NUMBER_OF_TRIALS = 'retries';

    const MESSAGE_STATUS_NEW = 2;
    const MESSAGE_STATUS_IN_PROGRESS = 3;
    const MESSAGE_STATUS_COMPLETE= 4;
    const MESSAGE_STATUS_RETRY_REQUIRED = 5;
    const MESSAGE_STATUS_ERROR = 6;

    /**
     * @var \Magento\MysqlMq\Model\Resource\Queue
     */
    protected $messageResource;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\MysqlMq\Model\Resource\Queue $messageResource
     */
    public function __construct(\Magento\MysqlMq\Model\Resource\Queue $messageResource)
    {
        $this->messageResource = $messageResource;
    }

    /**
     * Add message to all specified queues.
     *
     * @param string $topic
     * @param string $message
     * @param string[] $queueNames
     * @return $this
     */
    public function addMessageToQueues($topic, $message, $queueNames)
    {
        $messageId = $this->messageResource->saveMessage($topic, $message);
        $this->messageResource->linkQueues($messageId, $queueNames);
        return $this;
    }

    /**
     * Read the specified number of messages from the specified queue.
     *
     * If queue does not contain enough messages, method is not waiting for more messages.
     *
     * @param string $queue
     * @param int $maxMessagesNumber
     * @return array <pre>
     * [
     *     [
     *          self::MESSAGE_ID => $messageId,
     *          self::MESSAGE_QUEUE_ID => $queuId,
     *          self::MESSAGE_TOPIC => $topic,
     *          self::MESSAGE_BODY => $body,
     *          self::MESSAGE_STATUS => $status,
     *          self::MESSAGE_UPDATED_AT => $updatedAt,
     *          self::MESSAGE_QUEUE_NAME => $queueName
     *          self::MESSAGE_QUEUE_RELATION_ID => $relationId
     *     ],
     *     ...
     * ]</pre>
     */
    public function readMessages($queue, $maxMessagesNumber)
    {
        $selectedMessages = $this->messageResource->getMessages($queue, $maxMessagesNumber);
        /* The logic below allows to prevent the same message being processed by several consumers in parallel */
        foreach ($selectedMessages as $key => $message) {
            unset($selectedMessages[$key]);
            /* Set message status here to avoid extra reading from DB after it is updated */
            $message[self::MESSAGE_STATUS] = self::MESSAGE_STATUS_IN_PROGRESS;
            $selectedMessages[$message[self::MESSAGE_QUEUE_RELATION_ID]] = $message;
        }
        $selectedMessagesRelatedIds = array_keys($selectedMessages);
        $takenMessagesRelationIds = $this->messageResource->takeMessagesInProgress($selectedMessagesRelatedIds);
        $takenMessages = array_intersect_key($selectedMessages, array_flip($takenMessagesRelationIds));
        return $takenMessages;
    }

    /**
     * Push message back to queue for one more processing trial. Affects message in particular queue only.
     *
     * @param int $messageRelationId
     * @return void
     */
    public function pushToQueueForRetry($messageRelationId)
    {
        $this->messageResource->pushBackForRetry($messageRelationId);
    }

    /**
     * Change status of messages.
     *
     * @param int[] $messageRelationIds
     * @param int $status
     * @return void
     */
    public function changeStatus($messageRelationIds, $status)
    {
        $this->messageResource->changeStatus($messageRelationIds, $status);
    }
}
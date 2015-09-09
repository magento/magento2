<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MysqlMq\Model;

use Magento\Framework\Amqp\Config\Data as AmqpConfig;
use Magento\Framework\Amqp\PublisherInterface;
use Magento\MysqlMq\Model\Message;
use Magento\Framework\Amqp\MessageFactory;

/**
 * MySQL publisher implementation for message queue.
 */
class Publisher implements PublisherInterface
{
    /**
     * @var AmqpConfig
     */
    private $amqpConfig;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * Initialize dependencies.
     *
     * @param AmqpConfig $amqpConfig
     */
    public function __construct(AmqpConfig $amqpConfig, MessageFactory $messageFactory)
    {
        $this->amqpConfig = $amqpConfig;
        $this->messageFactory = $messageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($topicName, $data)
    {
        $queueNames = $this->amqpConfig->getQueuesForTopic($topicName);
        /** @var Message $message */
        $message = $this->messageFactory->create(
            [
                Message::KEY_BODY => $data,
                Message::KEY_TOPIC_NAME => $topicName,
                // TODO: Verify that updated_at is set automatically on save
            ]
        );
        // TODO: Consider saving all messages with a single request to DB for optimization
        $message->save();
        $message->getResource()->linkQueues($queueNames);
    }
}

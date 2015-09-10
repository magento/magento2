<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MysqlMq\Model;

use Magento\Framework\Amqp\Config\Data as AmqpConfig;
use Magento\Framework\Amqp\PublisherInterface;
use Magento\MysqlMq\Model\QueueManagement;

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
     * @var QueueManagement
     */
    private $queueManagement;

    /**
     * Initialize dependencies.
     *
     * @param AmqpConfig $amqpConfig
     * @param QueueManagement $queueManagement
     */
    public function __construct(AmqpConfig $amqpConfig, QueueManagement $queueManagement)
    {
        $this->amqpConfig = $amqpConfig;
        $this->queueManagement = $queueManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($topicName, $data)
    {
        $queueNames = $this->amqpConfig->getQueuesForTopic($topicName);
        $this->queueManagement->addMessageToQueues($topicName, $data, $queueNames);
    }
}

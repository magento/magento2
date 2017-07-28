<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Driver;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\ExchangeInterface;
use Magento\Framework\MessageQueue\ConfigInterface as MessageQueueConfig;
use Magento\MysqlMq\Model\QueueManagement;

/**
 * Class \Magento\MysqlMq\Model\Driver\Exchange
 *
 * @since 2.0.0
 */
class Exchange implements ExchangeInterface
{
    /**
     * @var MessageQueueConfig
     * @since 2.0.0
     */
    private $messageQueueConfig;

    /**
     * @var QueueManagement
     * @since 2.0.0
     */
    private $queueManagement;

    /**
     * Initialize dependencies.
     *
     * @param MessageQueueConfig $messageQueueConfig
     * @param QueueManagement $queueManagement
     * @since 2.0.0
     */
    public function __construct(MessageQueueConfig $messageQueueConfig, QueueManagement $queueManagement)
    {
        $this->messageQueueConfig = $messageQueueConfig;
        $this->queueManagement = $queueManagement;
    }

    /**
     * Send message
     *
     * @param string $topic
     * @param EnvelopeInterface $envelope
     * @return mixed
     * @since 2.0.0
     */
    public function enqueue($topic, EnvelopeInterface $envelope)
    {
        $queueNames = $this->messageQueueConfig->getQueuesByTopic($topic);
        $this->queueManagement->addMessageToQueues($topic, $envelope->getBody(), $queueNames);
        return null;
    }
}

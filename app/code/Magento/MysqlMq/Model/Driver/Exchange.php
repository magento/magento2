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

class Exchange implements ExchangeInterface
{
    /**
     * @var MessageQueueConfig
     */
    private $messageQueueConfig;

    /**
     * @var QueueManagement
     */
    private $queueManagement;

    /**
     * Initialize dependencies.
     *
     * @param MessageQueueConfig $messageQueueConfig
     * @param QueueManagement $queueManagement
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
     */
    public function enqueue($topic, EnvelopeInterface $envelope)
    {
        $queueNames = $this->messageQueueConfig->getQueuesByTopic($topic);
        $this->queueManagement->addMessageToQueues($topic, $envelope->getBody(), $queueNames);
        return null;
    }
}

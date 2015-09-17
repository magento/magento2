<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Driver;

use Magento\Framework\Amqp\EnvelopeInterface;
use Magento\Framework\Amqp\ExchangeInterface;
use Magento\Framework\Amqp\Config\Data as AmqpConfig;
use Magento\MysqlMq\Model\QueueManagement;

class Exchange implements ExchangeInterface
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
     * Send message
     *
     * @param string $topic
     * @param EnvelopeInterface $envelope
     */
    public function enqueue($topic, EnvelopeInterface $envelope)
    {
        $queueNames = $this->amqpConfig->getQueuesByTopic($topic);
        $this->queueManagement->addMessageToQueues($topic, $envelope->getBody(), $queueNames);
    }
}

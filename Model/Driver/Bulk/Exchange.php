<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Driver\Bulk;

use Magento\Framework\MessageQueue\Bulk\ExchangeInterface;
use Magento\Framework\MessageQueue\ConfigInterface as MessageQueueConfig;
use Magento\MysqlMq\Model\QueueManagement;

/**
 * Used to send messages in bulk in MySQL queue.
 * @since 2.2.0
 */
class Exchange implements ExchangeInterface
{
    /**
     * @var MessageQueueConfig
     * @since 2.2.0
     */
    private $messageQueueConfig;

    /**
     * @var QueueManagement
     * @since 2.2.0
     */
    private $queueManagement;

    /**
     * Initialize dependencies.
     *
     * @param MessageQueueConfig $messageQueueConfig
     * @param QueueManagement $queueManagement
     * @since 2.2.0
     */
    public function __construct(MessageQueueConfig $messageQueueConfig, QueueManagement $queueManagement)
    {
        $this->messageQueueConfig = $messageQueueConfig;
        $this->queueManagement = $queueManagement;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function enqueue($topic, array $envelopes)
    {
        $queueNames = $this->messageQueueConfig->getQueuesByTopic($topic);
        $messages = array_map(
            function ($envelope) {
                return $envelope->getBody();
            },
            $envelopes
        );
        $this->queueManagement->addMessagesToQueues($topic, $messages, $queueNames);

        return null;
    }
}

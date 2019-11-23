<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MysqlMq\Model\Driver;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\ExchangeInterface;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as MessageQueueConfig;
use Magento\MysqlMq\Model\ConnectionTypeResolver;
use Magento\MysqlMq\Model\QueueManagement;

/**
 * Class Exchange
 */
class Exchange implements ExchangeInterface
{
    /**
     * @var ConnectionTypeResolver
     */
    private $connectionTypeResolver;

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
     * @param ConnectionTypeResolver $connectionTypeResolver
     * @param MessageQueueConfig $messageQueueConfig
     * @param QueueManagement $queueManagement
     */
    public function __construct(
        ConnectionTypeResolver $connectionTypeResolver,
        MessageQueueConfig $messageQueueConfig,
        QueueManagement $queueManagement
    ) {
        $this->messageQueueConfig = $messageQueueConfig;
        $this->queueManagement = $queueManagement;
        $this->connectionTypeResolver = $connectionTypeResolver;
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
        $queueNames = [];
        $exchanges = $this->messageQueueConfig->getExchanges();
        foreach ($exchanges as $exchange) {
            $connection = $exchange->getConnection();
            if ($this->connectionTypeResolver->getConnectionType($connection)) {
                foreach ($exchange->getBindings() as $binding) {
                    if ($binding->getTopic() == $topic) {
                        $queueNames[] = $binding->getDestination();
                    }
                }
            }
        }
        $this->queueManagement->addMessageToQueues($topic, $envelope->getBody(), $queueNames);
        return null;
    }
}

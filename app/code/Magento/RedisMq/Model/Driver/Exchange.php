<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RedisMq\Model\Driver;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\ExchangeInterface;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as MessageQueueConfig;
use Magento\RedisMq\Model\ConnectionTypeResolver;

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
     * @var QueueFactory
     */
    private $queueFactory;

    /**
     * Exchange constructor.
     * @param ConnectionTypeResolver $connectionTypeResolver
     * @param MessageQueueConfig $messageQueueConfig
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        ConnectionTypeResolver $connectionTypeResolver,
        MessageQueueConfig $messageQueueConfig,
        QueueFactory $queueFactory
    ) {
        $this->messageQueueConfig = $messageQueueConfig;
        $this->connectionTypeResolver = $connectionTypeResolver;
        $this->queueFactory = $queueFactory;
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
        $exchanges = $this->messageQueueConfig->getExchanges();
        $isMatchedBindings = false;
        foreach ($exchanges as $exchange) {
            $connection = $exchange->getConnection();
            if ($this->connectionTypeResolver->getConnectionType($connection)) {
                foreach ($exchange->getBindings() as $binding) {
                    if ($binding->getTopic() == $topic) {
                        $isMatchedBindings = true;
                        $queue = $this->queueFactory->create($binding->getDestination(), $connection);
                        $queue->push($envelope);
                    }
                }
            }
        }
        if (!$isMatchedBindings) {
            $queue = $this->queueFactory->create($topic, 'redis');//todo get default
            $queue->push($envelope);
        }
    }
}

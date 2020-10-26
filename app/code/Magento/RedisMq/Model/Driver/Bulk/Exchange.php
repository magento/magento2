<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RedisMq\Model\Driver\Bulk;

use Magento\Framework\MessageQueue\Bulk\ExchangeInterface;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as MessageQueueConfig;
use Magento\RedisMq\Model\ConnectionTypeResolver;
use Magento\RedisMq\Model\Driver\QueueFactory;

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
     * @param string $topic
     * @param array $envelopes
     * @return mixed|void
     */
    public function enqueue($topic, array $envelopes)
    {
        $isMatchedBindings = false;
        foreach ($this->messageQueueConfig->getExchanges() as $exchange) {
            $connection = $exchange->getConnection();
            if ($this->connectionTypeResolver->getConnectionType($connection)) {
                foreach ($exchange->getBindings() as $binding) {
                    if ($binding->getTopic() == $topic) {
                        $isMatchedBindings = true;
                        $queue = $this->queueFactory->create($binding->getDestination(), $connection);
                        array_map(function ($envelope) use ($queue) {
                            $queue->push($envelope);
                        }, $envelopes);
                    }
                }
            }
        }
        if (!$isMatchedBindings) {
            $queue = $this->queueFactory->create($topic, 'redis');//todo get default
            array_map(function ($envelope) use ($queue) {
                $queue->push($envelope);
            }, $envelopes);
        }

        // throw new \Exception('No binding');
    }
}

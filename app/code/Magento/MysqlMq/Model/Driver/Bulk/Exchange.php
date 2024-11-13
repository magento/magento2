<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Model\Driver\Bulk;

use Magento\Framework\MessageQueue\Bulk\ExchangeInterface;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as MessageQueueConfig;
use Magento\MysqlMq\Model\ConnectionTypeResolver;
use Magento\MysqlMq\Model\QueueManagement;

/**
 * Used to send messages in bulk in MySQL queue.
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
     * @inheritdoc
     */
    public function enqueue($topic, array $envelopes)
    {
        $queueNames = [];
        $exchanges = $this->messageQueueConfig->getExchanges();
        foreach ($exchanges as $exchange) {
            $connection = $exchange->getConnection();
            if ($this->connectionTypeResolver->getConnectionType($connection)) {
                foreach ($exchange->getBindings() as $binding) {
                    if ($this->isMatchedBinding($binding, $topic)) {
                        $queueNames[] = $binding->getDestination();
                    }
                }
            }
        }

        $messages = array_map(
            function ($envelope) {
                return $envelope->getBody();
            },
            $envelopes
        );
        $this->queueManagement->addMessagesToQueues($topic, $messages, $queueNames);

        return null;
    }

    /**
     * Check if the binding is matched by the topic
     *
     * @param BindingInterface $binding
     * @param string $topic
     * @return bool
     */
    private function isMatchedBinding(BindingInterface $binding, string $topic): bool
    {
        $pattern = '/^' . str_replace(['.', '*', '#'], ['\.', '[^.]+?', '(.*?)'], $binding->getTopic() ?? '') . '$/';
        return preg_match($pattern, $topic) ? true : false;
    }
}

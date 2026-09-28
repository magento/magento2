<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Amqp\Model;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder;

/**
 * {@inheritdoc}
 *
 * @deprecated 100.2.0
 * @see Magento\Framework\MessageQueue
 */
class Exchange extends \Magento\Framework\Amqp\Exchange
{
    /**
     * Initialize dependencies.
     *
     * @param Config $amqpConfig
     * @param QueueConfig $queueConfig
     * @param CommunicationConfigInterface $communicationConfig
     * @param int $rpcConnectionTimeout
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Config $amqpConfig,
        QueueConfig $queueConfig,
        CommunicationConfigInterface $communicationConfig,
        $rpcConnectionTimeout = self::RPC_CONNECTION_TIMEOUT
    ) {
        parent::__construct(
            $amqpConfig,
            $this->getPublisherConfig(),
            $this->getResponseQueueNameBuilder(),
            $communicationConfig,
            $rpcConnectionTimeout
        );
    }

    /**
     * Get publisher config.
     *
     * @return PublisherConfig
     *
     * @deprecated 100.2.0
     * @see it's a private method, not used anymore
     */
    private function getPublisherConfig()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(PublisherConfig::class);
    }

    /**
     * Get response queue name builder.
     *
     * @return ResponseQueueNameBuilder
     *
     * @deprecated 100.2.0
     * @see it's a private method, not used anymore
     */
    private function getResponseQueueNameBuilder()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(ResponseQueueNameBuilder::class);
    }
}

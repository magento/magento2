<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Amqp\Model;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder;

/**
 * {@inheritdoc}
 *
 * @deprecated
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
     * @deprecated
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
     * @deprecated
     */
    private function getResponseQueueNameBuilder()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(ResponseQueueNameBuilder::class);
    }
}

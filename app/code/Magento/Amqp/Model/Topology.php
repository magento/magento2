<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Amqp\Model;

use Magento\Framework\Amqp\Topology\ExchangeInstaller;
use Magento\Framework\Amqp\Topology\QueueInstaller;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as TopologyConfig;
use Magento\Framework\Amqp\ConfigPool;
use Magento\Framework\Amqp\ConnectionTypeResolver;
use Magento\Framework\Amqp\TopologyInstaller;

/**
 * Class Topology creates topology for Amqp messaging
 *
 * @deprecated 100.2.0
 * @see Magento\Framework\MessageQueue
 */
class Topology extends TopologyInstaller
{
    /**
     * Type of exchange
     *
     * @deprecated
     * @see not used anymore
     */
    public const TOPIC_EXCHANGE = 'topic';

    public const AMQP_CONNECTION = 'amqp';

    /**
     * Durability for exchange and queue
     *
     * @deprecated
     * @see not used anymore
     */
    public const IS_DURABLE = true;

    /**
     * Initialize dependencies
     *
     * @param Config $amqpConfig
     * @param QueueConfig $queueConfig
     * @param CommunicationConfig $communicationConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Config $amqpConfig,
        QueueConfig $queueConfig,
        CommunicationConfig $communicationConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct(
            \Magento\Framework\App\ObjectManager::getInstance()->get(TopologyConfig::class),
            \Magento\Framework\App\ObjectManager::getInstance()->get(ExchangeInstaller::class),
            \Magento\Framework\App\ObjectManager::getInstance()->get(ConfigPool::class),
            \Magento\Framework\App\ObjectManager::getInstance()->get(QueueInstaller::class),
            \Magento\Framework\App\ObjectManager::getInstance()->get(ConnectionTypeResolver::class),
            $logger
        );
    }
}

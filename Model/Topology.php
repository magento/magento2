<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Amqp\Model;
use \Magento\Amqp\Model\Topology\ExchangeInstaller;
use \Magento\Amqp\Model\Topology\QueueInstaller;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use \Magento\Framework\MessageQueue\Topology\ConfigInterface as TopologyConfig;

/**
 * Class Topology creates topology for Amqp messaging
 *
 * @package Magento\Amqp\Model
 */
class Topology
{
    /**
     * Type of exchange
     * @deprecated
     */
    const TOPIC_EXCHANGE = 'topic';

    /**
     * Amqp connection
     */
    const AMQP_CONNECTION = 'amqp';

    /**
     * Durability for exchange and queue
     * @deprecated
     */
    const IS_DURABLE = true;

    /**
     * @var Config
     */
    private $amqpConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var TopologyConfig
     */
    private $topologyConfig;

    /**
     * @var \Magento\Amqp\Model\Topology\ExchangeInstaller
     */
    private $exchangeInstaller;

    /**
     * @var \Magento\Amqp\Model\Topology\QueueInstaller
     */
    private $queueInstaller;

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
        $this->amqpConfig = $amqpConfig;
        $this->logger = $logger;
    }

    /**
     * Get topology config
     *
     * @return TopologyConfig
     */
    private function getTopologyConfig()
    {
        if (null == $this->topologyConfig) {
            $this->topologyConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(TopologyConfig::class);
        }
        return $this->topologyConfig;
    }


    /**
     * Get exchange installer.
     *
     * @return ExchangeInstaller
     */
    private function getExchangeInstaller()
    {
        if (null == $this->exchangeInstaller) {
            $this->exchangeInstaller = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(ExchangeInstaller::class);
        }
        return $this->exchangeInstaller;
    }

    /**
     * Get queue installer.
     *
     * @return QueueInstaller
     */
    private function getQueueInstaller()
    {
        if (null == $this->queueInstaller) {
            $this->queueInstaller = \Magento\Framework\App\ObjectManager::getInstance()->get(QueueInstaller::class);
        }
        return $this->queueInstaller;
    }

    /**
     * Install Amqp Exchanges, Queues and bind them
     *
     * @return void
     */
    public function install()
    {
        try {
            foreach ($this->getTopologyConfig()->getQueues() as $queue) {
                $this->getQueueInstaller()->install($this->amqpConfig->getChannel(), $queue);
            }
            foreach ($this->getTopologyConfig()->getExchanges() as $exchange) {
                if ($exchange->getConnection() != self::AMQP_CONNECTION) {
                    continue;
                }
                $this->getExchangeInstaller()->install($this->amqpConfig->getChannel(), $exchange);
            }
        } catch (\PhpAmqpLib\Exception\AMQPExceptionInterface $e) {
            $this->logger->error('There is a problem. Error: ' . $e->getTraceAsString());
        }
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Amqp\Model;

use Magento\Framework\MessageQueue\Config\Data as QueueConfig;
use Magento\Framework\MessageQueue\Config\Converter as QueueConfigConverter;

/**
 * Class Topology creates topology for RabbitMq messaging
 *
 * @package Magento\Amqp\Model
 */
class Topology
{
    /**
     * Type of exchange
     */
    const TOPIC_EXCHANGE = 'topic';

    /**
     * RabbitMq connection
     */
    const RABBITMQ_CONNECTION = 'rabbitmq';

    /**
     * Durability for exchange and queue
     */
    const IS_DURABLE = true;

    /**
     * @var Config
     */
    private $rabbitMqConfig;

    /**
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * @var array
     */
    private $queueConfigData;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Initialize dependencies
     *
     * @param Config $rabbitMqConfig
     * @param QueueConfig $queueConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Config $rabbitMqConfig,
        QueueConfig $queueConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->rabbitMqConfig = $rabbitMqConfig;
        $this->queueConfig = $queueConfig;
        $this->logger = $logger;
    }

    /**
     * Install RabbitMq Exchanges, Queues and bind them
     *
     * @return void
     */
    public function install()
    {
        $queueConfig = $this->getQueueConfigData();
        if (isset($queueConfig[QueueConfigConverter::BINDS])) {
            $availableQueues = $this->getQueuesList(self::RABBITMQ_CONNECTION);
            $availableExchanges = $this->getExchangesList(self::RABBITMQ_CONNECTION);

            foreach ($queueConfig[QueueConfigConverter::BINDS] as $bind) {
                $queueName = $bind[QueueConfigConverter::BIND_QUEUE];
                $exchangeName = $bind[QueueConfigConverter::BIND_EXCHANGE];
                $topicName = $bind[QueueConfigConverter::BIND_TOPIC];
                if (in_array($queueName, $availableQueues) && in_array($exchangeName, $availableExchanges)) {
                    try {
                        $this->declareQueue($queueName);
                        $this->declareExchange($exchangeName);
                        $this->bindQueue($queueName, $exchangeName, $topicName);
                    } catch (\PhpAmqpLib\Exception\AMQPExceptionInterface $e) {
                        $this->logger->error(
                            sprintf(
                                'There is a problem with creating or binding queue "%s" and an exchange "%s". Error:',
                                $queueName,
                                $exchangeName,
                                $e->getTraceAsString()
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * Declare RabbitMq Queue
     *
     * @param string $queueName
     * @return void
     */
    private function declareQueue($queueName)
    {
        $this->getChannel()->queue_declare($queueName, false, self::IS_DURABLE, false, false);
    }

    /**
     * Declare RabbitMq Exchange
     *
     * @param string $exchangeName
     * @return void
     */
    private function declareExchange($exchangeName)
    {
        $this->getChannel()->exchange_declare($exchangeName, self::TOPIC_EXCHANGE, false, self::IS_DURABLE, false);
    }

    /**
     * Bind queue and exchange
     *
     * @param string $queueName
     * @param string $exchangeName
     * @param string $topicName
     * @return void
     */
    private function bindQueue($queueName, $exchangeName, $topicName)
    {
        $this->getChannel()->queue_bind($queueName, $exchangeName, $topicName);
    }

    /**
     * Return RabbitMq channel
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    private function getChannel()
    {
        return $this->rabbitMqConfig->getChannel();
    }

    /**
     * Return list of queue names, that are available for connection
     *
     * @param string $connection
     * @return array List of queue names
     */
    private function getQueuesList($connection)
    {
        $queues = [];
        $queueConfig = $this->getQueueConfigData();
        if (isset($queueConfig[QueueConfigConverter::CONSUMERS])) {
            foreach ($queueConfig[QueueConfigConverter::CONSUMERS] as $consumer) {
                if ($consumer[QueueConfigConverter::CONSUMER_CONNECTION] === $connection) {
                    $queues[] = $consumer[QueueConfigConverter::CONSUMER_QUEUE];
                }
            }
            $queues = array_unique($queues);
        }
        return $queues;
    }

    /**
     * Return list of exchange names, that are available for connection
     *
     * @param string $connection
     * @return array List of exchange names
     */
    private function getExchangesList($connection)
    {
        $exchanges = [];
        $queueConfig = $this->getQueueConfigData();
        if (isset($queueConfig[QueueConfigConverter::PUBLISHERS])) {
            foreach ($queueConfig[QueueConfigConverter::PUBLISHERS] as $consumer) {
                if ($consumer[QueueConfigConverter::PUBLISHER_CONNECTION] === $connection) {
                    $exchanges[] = $consumer[QueueConfigConverter::PUBLISHER_EXCHANGE];
                }
            }
            $exchanges = array_unique($exchanges);
        }
        return $exchanges;
    }

    /**
     * Returns the queue configuration.
     *
     * @return array
     */
    private function getQueueConfigData()
    {
        if ($this->queueConfigData == null) {
            $this->queueConfigData = $this->queueConfig->get();
        }
        return $this->queueConfigData;
    }
}

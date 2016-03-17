<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Amqp\Model;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Topology creates topology for Amqp messaging
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
     * Amqp connection
     */
    const AMQP_CONNECTION = 'amqp';

    /**
     * Durability for exchange and queue
     */
    const IS_DURABLE = true;

    /**
     * @var Config
     */
    private $amqpConfig;

    /**
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * @var CommunicationConfig
     */
    private $communicationConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Initialize dependencies
     *
     * @param Config $amqpConfig
     * @param QueueConfig $queueConfig
     * @param CommunicationConfig $communicationConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Config $amqpConfig,
        QueueConfig $queueConfig,
        CommunicationConfig $communicationConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->amqpConfig = $amqpConfig;
        $this->queueConfig = $queueConfig;
        $this->communicationConfig = $communicationConfig;
        $this->logger = $logger;
    }

    /**
     * Install Amqp Exchanges, Queues and bind them
     *
     * @return void
     */
    public function install()
    {
        $availableQueues = $this->getQueuesList(self::AMQP_CONNECTION);
        $availableExchanges = $this->getExchangesList(self::AMQP_CONNECTION);
        foreach ($this->queueConfig->getBinds() as $bind) {
            $queueName = $bind[QueueConfig::BIND_QUEUE];
            $exchangeName = $bind[QueueConfig::BIND_EXCHANGE];
            $topicName = $bind[QueueConfig::BIND_TOPIC];
            if (in_array($queueName, $availableQueues) && in_array($exchangeName, $availableExchanges)) {
                try {
                    $this->declareQueue($queueName);
                    $this->declareCallbackQueue($topicName);
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

    /**
     * Declare Amqp Queue
     *
     * @param string $queueName
     * @return void
     */
    private function declareQueue($queueName)
    {
        $this->getChannel()->queue_declare($queueName, false, self::IS_DURABLE, false, false);
    }

    /**
     * Declare Amqp Queue for Callback
     *
     * @param string $topicName
     * @return void
     */
    private function declareCallbackQueue($topicName)
    {
        if ($this->isSynchronousModeTopic($topicName)) {
            $callbackQueueName = $this->queueConfig->getResponseQueueName($topicName);
            $this->declareQueue($callbackQueueName);
        }
    }

    /**
     * Check whether the topic is in synchronous mode
     *
     * @param string $topicName
     * @return bool
     * @throws LocalizedException
     */
    private function isSynchronousModeTopic($topicName)
    {
        try {
            $topic = $this->communicationConfig->getTopic($topicName);
            $isSync = (bool)$topic[CommunicationConfig::TOPIC_IS_SYNCHRONOUS];
        } catch (LocalizedException $e) {
            throw new LocalizedException(__('Error while checking if topic is synchronous'));
        }
        return $isSync;
    }

    /**
     * Declare Amqp Exchange
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
     * Return Amqp channel
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    private function getChannel()
    {
        return $this->amqpConfig->getChannel();
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
        foreach ($this->queueConfig->getConsumers() as $consumer) {
            if ($consumer[QueueConfig::CONSUMER_CONNECTION] === $connection) {
                $queues[] = $consumer[QueueConfig::CONSUMER_QUEUE];
            }
        }
        foreach (array_keys($this->communicationConfig->getTopics()) as $topicName) {
            if ($this->queueConfig->getConnectionByTopic($topicName) === $connection) {
                $queues = array_merge($queues, $this->queueConfig->getQueuesByTopic($topicName));
            }
        }
        $queues = array_unique($queues);
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
        $queueConfig = $this->queueConfig->getPublishers();
        foreach ($queueConfig as $publisher) {
            if ($publisher[QueueConfig::PUBLISHER_CONNECTION] === $connection) {
                $exchanges[] = $publisher[QueueConfig::PUBLISHER_EXCHANGE];
            }
        }
        $exchanges = array_unique($exchanges);
        return $exchanges;
    }
}

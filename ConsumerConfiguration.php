<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\ConfigInterface as MessageQueueConfig;

/**
 * Value class which stores the configuration
 */
class ConsumerConfiguration implements ConsumerConfigurationInterface
{
    const CONSUMER_NAME = "consumer_name";
    const CONSUMER_TYPE = "consumer_type";
    const QUEUE_NAME = "queue_name";
    const MAX_MESSAGES = "max_messages";
    const SCHEMA_TYPE = "schema_type";
    const HANDLERS = 'handlers';

    const TYPE_SYNC = 'sync';
    const TYPE_ASYNC = 'async';

    /**
     * @var array
     */
    private $data;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var MessageQueueConfig
     */
    private $messageQueueConfig;

    /**
     * Initialize dependencies.
     *
     * @param QueueRepository $queueRepository
     * @param MessageQueueConfig $messageQueueConfig
     * @param array $data configuration data
     */
    public function __construct(QueueRepository $queueRepository, MessageQueueConfig $messageQueueConfig, $data = [])
    {
        $this->data = $data;
        $this->queueRepository = $queueRepository;
        $this->messageQueueConfig = $messageQueueConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumerName()
    {
        return $this->getData(self::CONSUMER_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxMessages()
    {
        return $this->getData(self::MAX_MESSAGES);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName()
    {
        return $this->getData(self::QUEUE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(self::CONSUMER_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getHandlers($topicName)
    {
        $output = $this->getData(self::HANDLERS);
        return isset($output[$topicName]) ? $output[$topicName] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTopicNames()
    {
        $output = $this->getData(self::HANDLERS);
        return is_array($output) && count($output) ? array_keys($output) : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getQueue()
    {
        $connectionName = $this->messageQueueConfig->getConnectionByConsumer($this->getConsumerName());
        return $this->queueRepository->get($connectionName, $this->getQueueName());
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageSchemaType($topicName)
    {
        return $this->messageQueueConfig->getMessageSchemaType($topicName);
    }

    /**
     * Get specified data item.
     *
     * @param string $key
     * @return string|null
     */
    private function getData($key)
    {
        if (!isset($this->data[$key])) {
            return null;
        }
        return $this->data[$key];
    }
}

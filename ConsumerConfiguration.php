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
    /**
     * @deprecated
     * @see ConsumerConfigurationInterface::TOPIC_TYPE
     */
    const CONSUMER_TYPE = "consumer_type";

    /**
     * @deprecated
     * @see ConsumerConfigurationInterface::TOPIC_HANDLERS
     */
    const HANDLERS = 'handlers';

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
        $topics = $this->getData(self::TOPICS);
        if (count($topics) > 1) {
            throw new \LogicException(
                'Current method is deprecated and does not support more than 1 topic declarations for consumer. '
                . 'Use \Magento\Framework\MessageQueue\ConsumerConfiguration::getConsumerType instead. '
                . "Multiple topics declared for consumer '{$this->getConsumerName()}'"
            );
        } else if (count($topics) < 1) {
            throw new \LogicException(
                "There must be at least one topic declared for consumer '{$this->getConsumerName()}'."
            );
        }
        // Get the only topic and read consumer type from its declaration. Necessary for backward compatibility
        $topicConfig = reset($topics);
        return $topicConfig[self::TOPIC_TYPE];
    }

    /**
     * {@inheritdoc}
     */
    public function getHandlers($topicName)
    {
        return $this->getTopicConfig($topicName)[self::TOPIC_HANDLERS];
    }

    /**
     * {@inheritdoc}
     */
    public function getTopicNames()
    {
        $topics = $this->getData(self::TOPICS);
        return is_array($topics) && count($topics) ? array_keys($topics) : [];
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
     * {@inheritdoc}
     */
    public function getConsumerType($topicName)
    {
        return $this->getTopicConfig($topicName)[self::TOPIC_TYPE];
    }

    /**
     * Get topic configuration for current consumer.
     *
     * @return array
     */
    private function getTopicConfig($topicName)
    {
        if (!isset($this->getData(self::TOPICS)[$topicName])) {
            throw new \LogicException("Consumer configuration for {$topicName} topic not found.");
        }
        return $this->getData(self::TOPICS)[$topicName];
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

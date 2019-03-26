<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\ConfigInterface as MessageQueueConfig;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;

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
     * @var ConsumerConfig
     */
    private $consumerConfig;

    /**
     * @var CommunicationConfig
     */
    private $communicationConfig;

    /**
     * Initialize dependencies.
     *
     * @param QueueRepository $queueRepository
     * @param MessageQueueConfig $messageQueueConfig
     * @param array $data configuration data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(QueueRepository $queueRepository, MessageQueueConfig $messageQueueConfig, $data = [])
    {
        $this->data = $data;
        $this->queueRepository = $queueRepository;
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
        } elseif (count($topics) < 1) {
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
        $connectionName = $this->getConsumerConfig()->getConsumer($this->getConsumerName())->getConnection();
        return $this->queueRepository->get($connectionName, $this->getQueueName());
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageSchemaType($topicName)
    {
        return $this->getCommunicationConfig()->getTopic($topicName)[CommunicationConfig::TOPIC_REQUEST_TYPE];
    }

    /**
     * Get topic configuration for current consumer.
     * @param string $topicName
     * @return array
     * @throws \LogicException
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

    /**
     * Get consumer config.
     *
     * @return ConsumerConfig
     *
     * @deprecated 102.0.1
     */
    private function getConsumerConfig()
    {
        if ($this->consumerConfig === null) {
            $this->consumerConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(ConsumerConfig::class);
        }
        return $this->consumerConfig;
    }

    /**
     * Get communication config.
     *
     * @return CommunicationConfig
     *
     * @deprecated 102.0.1
     */
    private function getCommunicationConfig()
    {
        if ($this->communicationConfig === null) {
            $this->communicationConfig = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(CommunicationConfig::class);
        }
        return $this->communicationConfig;
    }
}

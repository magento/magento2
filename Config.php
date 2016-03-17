<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\Data
     */
    protected $queueConfigData;

    /**
     * @param Config\Data $queueConfigData
     */
    public function __construct(Config\Data $queueConfigData)
    {
        $this->queueConfigData = $queueConfigData;
    }

    /**
     * @inheritDoc
     */
    public function getExchangeByTopic($topicName)
    {
        $publisherConfig = $this->getPublisherConfigByTopic($topicName);
        return isset($publisherConfig[ConfigInterface::PUBLISHER_EXCHANGE])
            ? $publisherConfig[ConfigInterface::PUBLISHER_EXCHANGE]
            : null;
    }

    /**
     * @inheritDoc
     */
    public function getQueuesByTopic($topic)
    {
        $exchange = $this->getExchangeByTopic($topic);
        /**
         * Exchange should be taken into account here to avoid retrieving queues, related to another exchange,
         * which is not currently associated with topic, but is configured in binds
         */
        $bindKey = $exchange . '--' . $topic;
        $output = $this->queueConfigData->get(ConfigInterface::EXCHANGE_TOPIC_TO_QUEUES_MAP . '/' . $bindKey);
        if (!$output) {
            throw new LocalizedException(
                new Phrase(
                    'No bindings configured for the "%topic" topic at "%exchange" exchange.',
                    ['topic' => $topic, 'exchange' => $exchange]
                )
            );
        }
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function getConnectionByTopic($topic)
    {
        $publisherConfig = $this->getPublisherConfigByTopic($topic);
        return isset($publisherConfig[ConfigInterface::PUBLISHER_CONNECTION])
            ? $publisherConfig[ConfigInterface::PUBLISHER_CONNECTION]
            : null;
    }

    /**
     * @inheritDoc
     */
    public function getConnectionByConsumer($consumer)
    {
        $connection = $this->queueConfigData->get(
            ConfigInterface::CONSUMERS . '/'. $consumer . '/'. ConfigInterface::CONSUMER_CONNECTION
        );
        if (!$connection) {
            throw new LocalizedException(
                new Phrase('Consumer "%consumer" has not connection.', ['consumer' => $consumer])
            );
        }
        return $connection;
    }

    /**
     * @inheritDoc
     */
    public function getMessageSchemaType($topic)
    {
        return $this->queueConfigData->get(
            ConfigInterface::TOPICS . '/' .
            $topic . '/' . ConfigInterface::TOPIC_SCHEMA . '/' . ConfigInterface::TOPIC_SCHEMA_TYPE
        );
    }

    /**
     * @inheritDoc
     */
    public function getConsumerNames()
    {
        $queueConfig = $this->queueConfigData->get(ConfigInterface::CONSUMERS, []);
        return array_keys($queueConfig);
    }

    /**
     * @inheritDoc
     */
    public function getConsumer($name)
    {

        return $this->queueConfigData->get(ConfigInterface::CONSUMERS . '/' . $name);
    }

    /**
     * @inheritDoc
     */
    public function getBinds()
    {
        return $this->queueConfigData->get(ConfigInterface::BINDS);
    }

    /**
     * @inheritDoc
     */
    public function getPublishers()
    {
        return $this->queueConfigData->get(ConfigInterface::PUBLISHERS, []);
    }

    /**
     * @inheritDoc
     */
    public function getConsumers()
    {
        return $this->queueConfigData->get(ConfigInterface::CONSUMERS, []);
    }

    /**
     * @inheritDoc
     */
    public function getTopic($name)
    {
        return $this->queueConfigData->get(ConfigInterface::TOPICS . '/' . $name);
    }

    /**
     * @inheritDoc
     */
    public function getPublisher($name)
    {
        return $this->queueConfigData->get(ConfigInterface::PUBLISHERS . '/' . $name);
    }

    /**
     * @inheritDoc
     */
    public function getResponseQueueName($topicName)
    {
        return ConfigInterface::RESPONSE_QUEUE_PREFIX . str_replace('-', '_', $topicName);
    }

    /**
     * Get publisher config by topic
     *
     * @param string $topicName
     * @return array|mixed|null
     * @throws LocalizedException
     */
    protected function getPublisherConfigByTopic($topicName)
    {
        $publisherName = $this->queueConfigData->get(
            ConfigInterface::TOPICS . '/' . $topicName . '/' . ConfigInterface::TOPIC_PUBLISHER
        );

        if (!$publisherName) {
            throw new LocalizedException(
                new Phrase('Message queue topic "%topic" is not configured.', ['topic' => $topicName])
            );
        }

        $publisherConfig = $this->queueConfigData->get(ConfigInterface::PUBLISHERS . '/' . $publisherName);
        if (!$publisherConfig) {
            throw new LocalizedException(
                new Phrase(
                    'Message queue publisher "%publisher" is not configured.',
                    ['publisher' => $publisherName]
                )
            );
        }
        return $publisherConfig;
    }
}

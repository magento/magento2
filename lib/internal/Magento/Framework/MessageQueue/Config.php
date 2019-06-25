<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Queue configuration.
 *
 * @deprecated 102.0.2
 */
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
     * @inheritdoc
     */
    public function getExchangeByTopic($topicName)
    {
        $publisherConfig = $this->getPublisherConfigByTopic($topicName);
        return $publisherConfig[ConfigInterface::PUBLISHER_EXCHANGE] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getQueuesByTopic($topic)
    {
        $publisherConfig = $this->getPublisherConfigByTopic($topic);
        $exchange = isset($publisherConfig[ConfigInterface::PUBLISHER_NAME])
            ? $publisherConfig[ConfigInterface::PUBLISHER_NAME]
            : null;
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
     * @inheritdoc
     */
    public function getConnectionByTopic($topic)
    {
        try {
            $publisherConfig = $this->getPublisherConfigByTopic($topic);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return null;
        }
        return $publisherConfig[ConfigInterface::PUBLISHER_CONNECTION] ?? null;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getMessageSchemaType($topic)
    {
        return $this->queueConfigData->get(
            ConfigInterface::TOPICS . '/' .
            $topic . '/' . ConfigInterface::TOPIC_SCHEMA . '/' . ConfigInterface::TOPIC_SCHEMA_TYPE
        );
    }

    /**
     * @inheritdoc
     */
    public function getConsumerNames()
    {
        $queueConfig = $this->queueConfigData->get(ConfigInterface::CONSUMERS, []);
        return array_keys($queueConfig);
    }

    /**
     * @inheritdoc
     */
    public function getConsumer($name)
    {

        return $this->queueConfigData->get(ConfigInterface::CONSUMERS . '/' . $name);
    }

    /**
     * @inheritdoc
     */
    public function getBinds()
    {
        return $this->queueConfigData->get(ConfigInterface::BINDS, []);
    }

    /**
     * @inheritdoc
     */
    public function getPublishers()
    {
        return $this->queueConfigData->get(ConfigInterface::PUBLISHERS, []);
    }

    /**
     * @inheritdoc
     */
    public function getConsumers()
    {
        return $this->queueConfigData->get(ConfigInterface::CONSUMERS, []);
    }

    /**
     * @inheritdoc
     */
    public function getTopic($name)
    {
        return $this->queueConfigData->get(ConfigInterface::TOPICS . '/' . $name);
    }

    /**
     * @inheritdoc
     */
    public function getPublisher($name)
    {
        return $this->queueConfigData->get(ConfigInterface::PUBLISHERS . '/' . $name);
    }

    /**
     * @inheritdoc
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

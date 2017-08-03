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
 * @deprecated 2.2.0
 * @since 2.1.0
 */
class Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\Data
     * @since 2.1.0
     */
    protected $queueConfigData;

    /**
     * @param Config\Data $queueConfigData
     * @since 2.1.0
     */
    public function __construct(Config\Data $queueConfigData)
    {
        $this->queueConfigData = $queueConfigData;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getExchangeByTopic($topicName)
    {
        $publisherConfig = $this->getPublisherConfigByTopic($topicName);
        return isset($publisherConfig[ConfigInterface::PUBLISHER_EXCHANGE])
            ? $publisherConfig[ConfigInterface::PUBLISHER_EXCHANGE]
            : null;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
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
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getConnectionByTopic($topic)
    {
        try {
            $publisherConfig = $this->getPublisherConfigByTopic($topic);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return null;
        }
        return isset($publisherConfig[ConfigInterface::PUBLISHER_CONNECTION])
            ? $publisherConfig[ConfigInterface::PUBLISHER_CONNECTION]
            : null;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
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
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getMessageSchemaType($topic)
    {
        return $this->queueConfigData->get(
            ConfigInterface::TOPICS . '/' .
            $topic . '/' . ConfigInterface::TOPIC_SCHEMA . '/' . ConfigInterface::TOPIC_SCHEMA_TYPE
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getConsumerNames()
    {
        $queueConfig = $this->queueConfigData->get(ConfigInterface::CONSUMERS, []);
        return array_keys($queueConfig);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getConsumer($name)
    {

        return $this->queueConfigData->get(ConfigInterface::CONSUMERS . '/' . $name);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getBinds()
    {
        return $this->queueConfigData->get(ConfigInterface::BINDS);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getPublishers()
    {
        return $this->queueConfigData->get(ConfigInterface::PUBLISHERS, []);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getConsumers()
    {
        return $this->queueConfigData->get(ConfigInterface::CONSUMERS, []);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getTopic($name)
    {
        return $this->queueConfigData->get(ConfigInterface::TOPICS . '/' . $name);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getPublisher($name)
    {
        return $this->queueConfigData->get(ConfigInterface::PUBLISHERS . '/' . $name);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
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
     * @since 2.1.0
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

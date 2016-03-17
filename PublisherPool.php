<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;

/**
 * Publishers pool.
 */
class PublisherPool implements PublisherInterface
{
    const MODE_SYNC = 'sync';
    const MODE_ASYNC = 'async';
    const CONNECTION_NAME = 'connectionName';
    const TYPE = 'type';

    /**
     * Publisher objects pool.
     *
     * @var \Magento\Framework\MessageQueue\PublisherInterface[]
     */
    protected $publishers = [];

    /**
     * Communication config.
     *
     * @var CommunicationConfig
     */
    protected $communicationConfig;

    /**
     * All of the merged queue config information
     *
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * Initialize dependencies.
     *
     * @param CommunicationConfig $communicationConfig
     * @param QueueConfig $queueConfig
     * @param string[] $publishers
     */
    public function __construct(
        CommunicationConfig $communicationConfig,
        QueueConfig $queueConfig,
        array $publishers
    ) {
        $this->communicationConfig = $communicationConfig;
        $this->queueConfig = $queueConfig;
        $this->initializePublishers($publishers);
    }

    /**
     * {@inheritdoc}
     */
    public function publish($topicName, $data)
    {
        /* read the topic configuration for the publisher name */
        $publisherName = $this->getPublisherNameForTopic($topicName);
        $publisherConfig = $this->getPublisherConfig($publisherName);
        $topic = $this->queueConfig->getTopic($topicName);
        $type = $topic[CommunicationConfig::TOPIC_IS_SYNCHRONOUS] ? self::MODE_SYNC : self::MODE_ASYNC;
        /** @var PublisherInterface $publisher */
        $publisher = $this->getPublisherForConnectionNameAndType(
            $type,
            $publisherConfig[QueueConfig::PUBLISHER_CONNECTION]
        );
        return $publisher->publish($topicName, $data);

    }

    /**
     * Initialize publisher objects pool.
     *
     * @param array $publishers
     * @return void
     */
    private function initializePublishers(array $publishers)
    {
        $asyncPublishers = isset($publishers[self::MODE_ASYNC]) ? $publishers[self::MODE_ASYNC] : [];
        $syncPublishers = isset($publishers[self::MODE_SYNC]) ? $publishers[self::MODE_SYNC] : [];
        foreach ($asyncPublishers as $publisherConfig) {
            $this->addPublisherToPool(
                self::MODE_ASYNC,
                $publisherConfig[self::CONNECTION_NAME],
                $publisherConfig[self::TYPE]
            );
        }
        foreach ($syncPublishers as $publisherConfig) {
            $this->addPublisherToPool(
                self::MODE_SYNC,
                $publisherConfig[self::CONNECTION_NAME],
                $publisherConfig[self::TYPE]
            );
        }
    }

    /**
     * Add publisher.
     *
     * @param string $type
     * @param string $connectionName
     * @param PublisherInterface $publisher
     * @return $this
     */
    private function addPublisherToPool($type, $connectionName, PublisherInterface $publisher)
    {
        $this->publishers[$type][$connectionName] = $publisher;
        return $this;
    }

    /**
     * Return the publisher name given a topic.
     *
     * @param string $topicName
     * @return string
     * @throws \LogicException
     */
    private function getPublisherNameForTopic($topicName)
    {
        $topicConfig = $this->queueConfig->getTopic($topicName);
        if ($topicConfig === null) {
            throw new \LogicException(sprintf('Specified topic "%s" is not declared.', $topicName));
        }
        return $topicConfig[QueueConfig::TOPIC_PUBLISHER];
    }

    /**
     * Returns the publisher configuration information.
     *
     * @param string $publisherName
     * @return array
     * @throws \LogicException
     */
    private function getPublisherConfig($publisherName)
    {
        $publisherConfig = $this->queueConfig->getPublisher($publisherName);
        if ($publisherConfig === null) {
            throw new \LogicException(sprintf('Specified publisher "%s" is not declared.', $publisherName));
        }
        return $publisherConfig;
    }

    /**
     * Return an instance of a publisher for a connection name.
     *
     * @param string $type
     * @param string $connectionName
     * @return PublisherInterface
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function getPublisherForConnectionNameAndType($type, $connectionName)
    {
        if (!isset($this->publishers[$type])) {
            throw new \InvalidArgumentException('Unknown publisher type ' . $type);
        }

        if (!isset($this->publishers[$type][$connectionName])) {
            throw new \LogicException(
                sprintf(
                    'Could not find an implementation type for type "%s" and connection "%s".',
                    $type,
                    $connectionName
                )
            );
        }
        return $this->publishers[$type][$connectionName];
    }
}

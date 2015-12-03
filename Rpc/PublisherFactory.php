<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Rpc;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\MessageQueue\Config\Converter as QueueConfigConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class which creates Publishers
 */
class PublisherFactory
{
    /**
     * All of the merged queue config information
     *
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * @var PublisherInterface[]
     */
    private $publishers;

    /**
     * Initialize dependencies.
     *
     * @param QueueConfig $queueConfig
     * @param array $publishers
     */
    public function __construct(
        QueueConfig $queueConfig,
        $publishers = []
    ) {
        $this->queueConfig = $queueConfig;
        $this->publishers = [];

        foreach ($publishers as $publisherConfig) {
            $this->add($publisherConfig['connectionName'], $publisherConfig['type']);
        }
    }

    /**
     * Add publisher.
     *
     * @param string $name
     * @param PublisherInterface $publisher
     * @return $this
     */
    private function add($name, PublisherInterface $publisher)
    {
        $this->publishers[$name] = $publisher;
        return $this;
    }

    /**
     * Retrieves the queue configuration and returns a concrete publisher.
     *
     * @param string $topicName
     * @return PublisherInterface
     */
    public function create($topicName)
    {
        /* read the topic configuration for the publisher name */
        $publisherName = $this->getPublisherNameForTopic($topicName);

        $publisherConfig = $this->getPublisherConfigForName($publisherName);
        $publisher = $this->getPublisherForConnectionName($publisherConfig[QueueConfigConverter::PUBLISHER_CONNECTION]);
        return $publisher;
    }

    /**
     * Return an instance of a publisher for a connection name.
     *
     * @param string $connectionName
     * @return PublisherInterface
     * @throws LocalizedException
     */
    private function getPublisherForConnectionName($connectionName)
    {
        if (isset($this->publishers[$connectionName])) {
            return $this->publishers[$connectionName];
        }
        throw new LocalizedException(
            new Phrase('Could not find an implementation type for connection "%name".', ['name' => $connectionName])
        );
    }

    /**
     * Returns the publisher configuration information.
     *
     * @param string $publisherName
     * @return array
     * @throws LocalizedException
     */
    private function getPublisherConfigForName($publisherName)
    {
        $publisherConfig = $this->queueConfig->getPublisher($publisherName);
        if (is_null($publisherConfig)) {
            throw new LocalizedException(
                new Phrase('Specified publisher "%publisher" is not declared.', ['publisher' => $publisherName])
            );
        }
        return $publisherConfig;
    }

    /**
     * Return the publisher name given a topic.
     *
     * @param string $topicName
     * @return string
     * @throws LocalizedException
     */
    private function getPublisherNameForTopic($topicName)
    {
        $topicConfig = $this->queueConfig->getTopic($topicName);
        if (is_null($topicConfig)) {
            throw new LocalizedException(
                new Phrase('Specified topic "%topic" is not declared.', ['topic' => $topicName])
            );
        }
        return $topicConfig[QueueConfigConverter::TOPIC_PUBLISHER];

    }
}

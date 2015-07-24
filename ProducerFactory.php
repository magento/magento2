<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\Amqp\Config\Data as QueueConfig;
use Magento\Framework\Amqp\Config\Converter as QueueConfigConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;

/**
 * Class which creates Producers
 */
class ProducerFactory
{
    /**
     * A map from connection name to the implementation class. Probably should read this from a config file eventually.
     *
     * @var array
     */
    private $connectionNameToClassMap = [
        'rabbitmq' => 'Magento\Framework\Amqp\AmqpProducer'
    ];

    /**
     * All of the merged queue config information
     *
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Initialize dependencies.
     *
     * @param QueueConfig $queueConfig
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        QueueConfig $queueConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->queueConfig = $queueConfig;
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieves the queue configuration and returns an initialized a producer.
     *
     * @param string $topicName
     * @return ProducerInterface
     */
    public function create($topicName)
    {
        /* read the topic configuration for the publisher name */
        $publisherName = $this->getPublisherNameForTopic($topicName);

        $publisherConfig = $this->getPublisherConfigForName($publisherName);
        $producerTypeName = $this->getProducerTypeName($publisherConfig[QueueConfigConverter::PUBLISHER_CONNECTION]);
        return $this->objectManager->create($producerTypeName, [ 'config' => $publisherConfig ]);
    }

    /**
     * Return the class type of producer to create.
     *
     * @param string $connectionName
     * @return string
     */
    private function getProducerTypeName($connectionName)
    {
        if (isset($this->connectionNameToClassMap[$connectionName])) {
            return $this->connectionNameToClassMap[$connectionName];
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
     */
    private function getPublisherConfigForName($publisherName)
    {
        $queueConfig = $this->queueConfig->get();
        if (isset($queueConfig[QueueConfigConverter::PUBLISHERS][$publisherName])) {
            return $queueConfig[QueueConfigConverter::PUBLISHERS][$publisherName];
        }
        throw new LocalizedException(
            new Phrase('Specified publisher "%publisher" is not declared.', ['publisher' => $publisher])
        );
    }

    /**
     * Return the publisher name given a topic.
     *
     * @param string $topicName
     * @return string
     * @throws LocalizedException
     */
    protected function getPublisherNameForTopic($topicName)
    {
        /* TODO: Probably should have the queueConfig itself figure out if there's a runtime environment configuration
           to override a particular queue's publisher */
        $queueConfig = $this->queueConfig->get();
        if (isset($queueConfig[QueueConfigConverter::TOPICS][$topicName])) {
            return $queueConfig[QueueConfigConverter::TOPICS][$topicName][QueueConfigConverter::TOPIC_PUBLISHER];
        }
        throw new LocalizedException(new Phrase('Specified topic "%topic" is not declared.', ['topic' => $topicName]));
    }
}

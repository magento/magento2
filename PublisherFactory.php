<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\Amqp\Config\Data as QueueConfig;
use Magento\Framework\Amqp\Config\Converter as QueueConfigConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\Helper\Composite as CompositeHelper;
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
     * <type name="Magento\Framework\Amqp\ProducerFactory">
     *     <arguments>
     *         <argument name="publishers" xsi:type="array">
     *             <item name="rabbitmq" xsi:type="array">
     *                 <item name="type" xsi:type="object">Magento\Framework\Amqp\AmqpProducer</item>
     *                 <item name="connectionName" xsi:type="string">rabbitmq</item>
     *                 <item name="sortOrder" xsi:type="string">10</item>
     *             </item>
     *         </argument>
     *     </arguments>
     * </type>
     *
     * @param QueueConfig $queueConfig
     * @param CompositeHelper $compositeHelper
     * @param PublisherInterface[] $publishers
     */
    public function __construct(
        QueueConfig $queueConfig,
        CompositeHelper $compositeHelper,
        $publishers = []
    ) {
        $this->queueConfig = $queueConfig;
        $this->publishers = [];

        $publishers = $compositeHelper->filterAndSortDeclaredComponents($publishers);
        foreach ($publishers as $name => $publisherConfig) {
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
     * Return the class type of publisher to create.
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
        $queueConfig = $this->queueConfig->get();
        if (isset($queueConfig[QueueConfigConverter::PUBLISHERS][$publisherName])) {
            return $queueConfig[QueueConfigConverter::PUBLISHERS][$publisherName];
        }
        throw new LocalizedException(
            new Phrase('Specified publisher "%publisher" is not declared.', ['publisher' => $publisherName])
        );
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
        /* TODO: Probably should have the queueConfig itself figure out if there's a runtime environment configuration
           to override a particular queue's publisher */
        $queueConfig = $this->queueConfig->get();
        if (isset($queueConfig[QueueConfigConverter::TOPICS][$topicName])) {
            return $queueConfig[QueueConfigConverter::TOPICS][$topicName][QueueConfigConverter::TOPIC_PUBLISHER];
        }
        throw new LocalizedException(new Phrase('Specified topic "%topic" is not declared.', ['topic' => $topicName]));
    }
}

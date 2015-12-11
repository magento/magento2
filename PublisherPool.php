<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Publishers pool.
 */
class PublisherPool
{
    const SYNC_MODE = 'sync';
    const ASYNC_MODE = 'async';
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
     * @var \Magento\Framework\Communication\Config
     */
    protected $config;

    /**
     * All of the merged queue config information
     *
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * Initialize dependencies.
     *
     *  <type name="Magento\Framework\MessageQueue\PublisherPool">
     *      <arguments>
     *          <argument name="publishers" xsi:type="array">
     *              <item name="async" xsi:type="array">
     *                  <item name="amqp" xsi:type="array">
     *                      <item name="type" xsi:type="object">Magento\Framework\MessageQueue\Rpc\Publisher</item>
     *                      <item name="connectionName" xsi:type="string">amqp</item>
     *                  </item>
     *                  <item name="mysql" xsi:type="array">
     *                      <item name="type" xsi:type="object">Magento\Framework\MessageQueue\Publisher</item>
     *                      <item name="connectionName" xsi:type="string">db</item>
     *                  </item>
     *              </item>
     *              <item name="sync" xsi:type="array">
     *                  <item name="amqp" xsi:type="array">
     *                      <item name="type" xsi:type="object">Magento\Framework\MessageQueue\Rpc\Publisher</item>
     *                      <item name="connectionName" xsi:type="string">amqp</item>
     *                  </item>
     *              </item>
     *          </argument>
     *      </arguments>
     *  </type>
     *
     * @param \Magento\Framework\Communication\Config $config
     * @param QueueConfig $queueConfig
     * @param string[] $publishers
     */
    public function __construct(
        \Magento\Framework\Communication\Config $config,
        QueueConfig $queueConfig,
        array $publishers
    ) {
        $this->config = $config;
        $this->queueConfig = $queueConfig;
        $this->initializePublishers($publishers);
    }

    /**
     * Get publisher by topic.
     *
     * @param string $topicName
     * @return PublisherInterface
     */
    public function getByTopicType($topicName)
    {
        $topic = $this->config->getTopic($topicName);
        /* read the topic configuration for the publisher name */
        $publisherName = $this->getPublisherNameForTopic($topicName);
        $publisherConfig = $this->getPublisherConfigForName($publisherName);
        $type = $topic[\Magento\Framework\Communication\ConfigInterface::TOPIC_IS_SYNCHRONOUS] ? self::SYNC_MODE
            : self::ASYNC_MODE;
        return $this->getPublisherForConnectionNameAndType($type, $publisherConfig[QueueConfig::PUBLISHER_CONNECTION]);
    }

    /**
     * Initialize publisher objects pool.
     *
     * @param array $publishers
     */
    private function initializePublishers(array $publishers)
    {
        $asyncPublishers = isset($publishers[self::ASYNC_MODE]) ? $publishers[self::ASYNC_MODE] : [];
        $syncPublishers = isset($publishers[self::SYNC_MODE]) ? $publishers[self::SYNC_MODE] : [];
        foreach ($asyncPublishers as $publisherConfig) {
            $this->addPublisherToPool(
                self::ASYNC_MODE,
                $publisherConfig[self::CONNECTION_NAME],
                $publisherConfig[self::TYPE]
            );
        }
        foreach ($syncPublishers as $publisherConfig) {
            $this->addPublisherToPool(
                self::SYNC_MODE,
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
     * @throws LocalizedException
     */
    private function getPublisherNameForTopic($topicName)
    {
        $topicConfig = $this->queueConfig->getTopic($topicName);
        if ($topicConfig === null) {
            throw new LocalizedException(
                new Phrase('Specified topic "%topic" is not declared.', ['topic' => $topicName])
            );
        }
        return $topicConfig[QueueConfig::TOPIC_PUBLISHER];
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
        if ($publisherConfig === null) {
            throw new LocalizedException(
                new Phrase('Specified publisher "%publisher" is not declared.', ['publisher' => $publisherName])
            );
        }
        return $publisherConfig;
    }

    /**
     * Return an instance of a publisher for a connection name.
     *
     * @param string $type
     * @param string $connectionName
     * @return PublisherInterface
     * @throws LocalizedException
     */
    private function getPublisherForConnectionNameAndType($type, $connectionName)
    {
        if (!isset($this->publishers[$type])) {
            throw new \InvalidArgumentException('Unknown publisher type ' . $type);
        }

        if (!isset($this->publishers[$type][$connectionName])) {
            throw new LocalizedException(
                new Phrase(
                    'Could not find an implementation type for type "%type" and connection "%name".',
                    [
                        'type' => $type,
                        'name' => $connectionName
                    ]
                )
            );
        }
        return $this->publishers[$type][$connectionName];
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;

/**
 * Publishers pool.
 *
 * @api
 * @since 102.0.2
 */
class PublisherPool implements PublisherInterface, BulkPublisherInterface
{
    const MODE_SYNC = 'sync';
    const MODE_ASYNC = 'async';

    /**
     * @deprecated
     */
    const TYPE = 'type';

    /**
     * @deprecated
     */
    const CONNECTION_NAME = 'connectionName';

    /**
     * Publisher objects pool.
     *
     * @var \Magento\Framework\MessageQueue\PublisherInterface[]
     * @since 102.0.2
     */
    protected $publishers = [];

    /**
     * Communication config.
     *
     * @var CommunicationConfig
     * @since 102.0.2
     */
    protected $communicationConfig;

    /**
     * @var PublisherConfig
     */
    private $publisherConfig;

    /**
     * @var ConnectionTypeResolver
     */
    private $connectionTypeResolver;

    /**
     * Initialize dependencies.
     *
     * @param CommunicationConfig $communicationConfig
     * @param QueueConfig $queueConfig
     * @param string[] $publishers
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        CommunicationConfig $communicationConfig,
        QueueConfig $queueConfig,
        array $publishers
    ) {
        $this->communicationConfig = $communicationConfig;
        $this->initializePublishers($publishers);
    }

    /**
     * {@inheritdoc}
     * @since 102.0.2
     */
    public function publish($topicName, $data)
    {
        $publisherType = $this->communicationConfig->getTopic($topicName)[CommunicationConfig::TOPIC_IS_SYNCHRONOUS]
            ? self::MODE_SYNC
            : self::MODE_ASYNC;
        $connectionName = $this->getPublisherConfig()->getPublisher($topicName)->getConnection()->getName();
        $publisher = $this->getPublisherForConnectionNameAndType($publisherType, $connectionName);
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
        foreach ($asyncPublishers as $connectionType => $publisher) {
            $this->addPublisherToPool(
                self::MODE_ASYNC,
                $connectionType,
                $publisher
            );
        }
        foreach ($syncPublishers as $connectionType => $publisher) {
            $this->addPublisherToPool(
                self::MODE_SYNC,
                $connectionType,
                $publisher
            );
        }
    }

    /**
     * Add publisher.
     *
     * @param string $type
     * @param string $connectionType
     * @param PublisherInterface $publisher
     * @return $this
     */
    private function addPublisherToPool($type, $connectionType, PublisherInterface $publisher)
    {
        $this->publishers[$type][$connectionType] = $publisher;
        return $this;
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
        $connectionType = $this->getConnectionTypeResolver()->getConnectionType($connectionName);
        if (!isset($this->publishers[$type])) {
            throw new \InvalidArgumentException('Unknown publisher type ' . $type);
        }

        if (!isset($this->publishers[$type][$connectionType])) {
            throw new \LogicException(
                sprintf(
                    'Could not find an implementation type for type "%s" and connection "%s".',
                    $type,
                    $connectionName
                )
            );
        }
        return $this->publishers[$type][$connectionType];
    }

    /**
     * Get publisher config.
     *
     * @return PublisherConfig
     *
     * @deprecated 102.0.2
     */
    private function getPublisherConfig()
    {
        if ($this->publisherConfig === null) {
            $this->publisherConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(PublisherConfig::class);
        }
        return $this->publisherConfig;
    }

    /**
     * Get connection type resolver.
     *
     * @return ConnectionTypeResolver
     *
     * @deprecated 102.0.2
     */
    private function getConnectionTypeResolver()
    {
        if ($this->connectionTypeResolver === null) {
            $this->connectionTypeResolver = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(ConnectionTypeResolver::class);
        }
        return $this->connectionTypeResolver;
    }
}

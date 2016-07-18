<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConnectionFactory;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItemFactory;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItemInterface;
use Magento\Framework\MessageQueue\Publisher\Config\Data as ConfigData;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConnectionInterface;
use Magento\Framework\Phrase;

/**
 * {@inheritdoc}
 */
class Config implements ConfigInterface
{
    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * @var PublisherConfigItemFactory
     */
    private $publisherConfigItemFactory;

    /**
     * @var PublisherConnectionFactory
     */
    private $publishedConnectionFactory;

    /**
     * Initialize dependencies.
     *
     * @param ConfigData $configData
     * @param PublisherConfigItemFactory $publisherConfigItemFactory
     * @param PublisherConnectionFactory $handlerFactory
     */
    public function __construct(
        ConfigData $configData,
        PublisherConfigItemFactory $publisherConfigItemFactory,
        PublisherConnectionFactory $handlerFactory
    ) {
        $this->configData = $configData;
        $this->publisherConfigItemFactory = $publisherConfigItemFactory;
        $this->publishedConnectionFactory = $handlerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublisher($name)
    {
        $publisherData = $this->configData->get($name);
        if (!$publisherData) {
            throw new LocalizedException(new Phrase("Publisher '%publisher' is not declared.", ['publisher' => $name]));
        }
        return $this->createPublisherConfigItem($publisherData);
    }

    /**
     * {@inheritdoc}
     */
    public function getPublishers()
    {
        $publisherConfigItems = [];
        foreach ($this->configData as $publisherName => $publisherData) {
            $publisherConfigItems[$publisherName] = $this->createPublisherConfigItem($publisherData);
        }
        return $publisherConfigItems;
    }

    /**
     * Create publisher config item object based on provided publisher data.
     *
     * @param array $publisherData
     * @return PublisherConfigItemInterface
     */
    private function createPublisherConfigItem($publisherData)
    {
        $handlers = [];
        foreach ($publisherData['connections'] as $connectionConfig) {
            $handlers[] = $this->createPublisherConnection($connectionConfig);
        }
        return $this->publisherConfigItemFactory->create([
            'topic' => $publisherData['topic'],
            'isDisabled' => $publisherData['isDisabled'],
            'connections' => $handlers,
        ]);
    }

    /**
     * Create publisher connection config item object based on provided connection data.
     *
     * @param array $connection
     * @return PublisherConnectionInterface
     */
    private function createPublisherConnection($connection)
    {
        return $this->publishedConnectionFactory->create(
            [
                'name' => $connection['name'],
                'exchange' => $connection['exchange'],
                'isDisabled' => $connection['isDisabled'],
            ]
        );
    }
}

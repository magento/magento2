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
     * Publishers config data.
     *
     * @var ConfigData
     */
    private $configData;

    /**
     * Publisher config item factory.
     *
     * @var PublisherConfigItemFactory
     */
    private $publisherConfigItemFactory;

    /**
     * Publisher connection factory.
     *
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

        $publisher = $this->createPublisherConfigItem($publisherData);

        if ($publisher->isDisabled()) {
            throw new LocalizedException(new Phrase("Publisher '%publisher' is not declared.", ['publisher' => $name]));
        }
        return $publisher;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublishers()
    {
        $publisherConfigItems = [];
        foreach ($this->configData->get() as $publisherName => $publisherData) {
            $publisher = $this->createPublisherConfigItem($publisherData);
            if ($publisher->isDisabled()) {
                continue;
            }
            $publisherConfigItems[$publisherName] = $publisher;
        }
        return $publisherConfigItems;
    }

    /**
     * Create publisher config item object based on provided publisher data.
     *
     * @param array $publisherData
     * @return PublisherConfigItemInterface
     */
    private function createPublisherConfigItem(array $publisherData)
    {
        $connection = null;
        foreach ($publisherData['connections'] as $connectionConfig) {
            if (!$connectionConfig['disabled']) {
                $connection = $this->createPublisherConnection($connectionConfig);
                break;
            }
        }
        if (null === $connection) {
            $connection = $this->createPublisherConnection(
                [
                    'name' => 'amqp',
                    'exchange' => 'magento',
                    'disabled' => false,
                ]
            );
        }
        return $this->publisherConfigItemFactory->create([
            'topic' => $publisherData['topic'],
            'disabled' => $publisherData['disabled'],
            'connection' => $connection,
        ]);
    }

    /**
     * Create publisher connection config item object based on provided connection data.
     *
     * @param string[] $connection
     * @return PublisherConnectionInterface
     */
    private function createPublisherConnection($connection)
    {
        return $this->publishedConnectionFactory->create(
            [
                'name' => $connection['name'],
                'exchange' => $connection['exchange'],
                'disabled' => $connection['disabled'],
            ]
        );
    }
}

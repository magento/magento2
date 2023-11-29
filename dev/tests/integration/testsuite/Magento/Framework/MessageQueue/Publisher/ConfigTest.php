<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher;

use Magento\Framework\MessageQueue\DefaultValueProvider;

/**
 * Test of queue publisher configuration reading and parsing.
 *
 * @magentoCache config disabled
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DefaultValueProvider
     */
    private $defaultValueProvider;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->defaultValueProvider = $this->objectManager->get(DefaultValueProvider::class);
    }

    public function testGetPublishersWithOneEnabledConnection()
    {
        /** @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Publisher\ConfigInterface::class);

        $publishers = $config->getPublishers();
        $publisher = $config->getPublisher('topic.message.queue.config.01');
        $itemFromList = null;
        foreach ($publishers as $item) {
            if ($item->getTopic() == 'topic.message.queue.config.01') {
                $itemFromList = $item;
                break;
            }
        }

        $this->assertEquals($publisher, $itemFromList, 'Inconsistent publisher object');

        $this->assertEquals('topic.message.queue.config.01', $publisher->getTopic(), 'Incorrect topic name');
        $this->assertFalse($publisher->isDisabled(), 'Incorrect publisher state');
        /** @var \Magento\Framework\MessageQueue\Publisher\Config\PublisherConnectionInterface $connection */
        $connection = $publisher->getConnection();
        $this->assertEquals('amqp', $connection->getName(), 'Incorrect connection name');
        $this->assertEquals('magento2', $connection->getExchange(), 'Incorrect exchange name');
        $this->assertFalse($connection->isDisabled(), 'Incorrect connection status');
    }

    public function testGetPublisherConnectionWithoutConfiguredExchange()
    {
        /** @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Publisher\ConfigInterface::class);

        $publisher = $config->getPublisher('topic.message.queue.config.04');
        $connection = $publisher->getConnection();
        $this->assertEquals('magento', $connection->getExchange(), 'Incorrect exchange name');
    }

    public function testGetPublishersWithoutEnabledConnection()
    {
        /** @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Publisher\ConfigInterface::class);

        $publisher = $config->getPublisher('topic.message.queue.config.02');

        $this->assertEquals('topic.message.queue.config.02', $publisher->getTopic(), 'Incorrect topic name');
        $this->assertFalse($publisher->isDisabled(), 'Incorrect publisher state');

        /** @var \Magento\Framework\MessageQueue\Publisher\Config\PublisherConnectionInterface $connection */
        $connection = $publisher->getConnection();
        $this->assertEquals(
            $this->defaultValueProvider->getConnection(),
            $connection->getName(),
            'Incorrect default connection name'
        );
        $this->assertEquals('magento', $connection->getExchange(), 'Incorrect default exchange name');
        $this->assertFalse($connection->isDisabled(), 'Incorrect connection status');
    }

    /**
     */
    public function testGetDisabledPublisherThrowsException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Publisher \'topic.message.queue.config.03\' is not declared.');

        /** @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Publisher\ConfigInterface::class);
        $config->getPublisher('topic.message.queue.config.03');
    }
}

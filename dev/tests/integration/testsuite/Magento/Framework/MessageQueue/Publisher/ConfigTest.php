<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher;

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

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
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

        $this->assertSame($publisher, $itemFromList, 'Inconsistent publisher object');

        $this->assertSame('topic.message.queue.config.01', $publisher->getTopic(), 'Incorrect topic name');
        $this->assertFalse($publisher->isDisabled(), 'Incorrect publisher state');
        /** @var \Magento\Framework\MessageQueue\Publisher\Config\PublisherConnectionInterface $connection */
        $connection = $publisher->getConnection();
        $this->assertSame('amqp', $connection->getName(), 'Incorrect connection name');
        $this->assertSame('magento2', $connection->getExchange(), 'Incorrect exchange name');
        $this->assertFalse($connection->isDisabled(), 'Incorrect connection status');
    }

    public function testGetPublisherConnectionWithoutConfiguredExchange()
    {
        /** @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Publisher\ConfigInterface::class);

        $publisher = $config->getPublisher('topic.message.queue.config.04');
        $connection = $publisher->getConnection();
        $this->assertSame('magento', $connection->getExchange(), 'Incorrect exchange name');
    }

    public function testGetPublishersWithoutEnabledConnection()
    {
        /** @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Publisher\ConfigInterface::class);

        $publisher = $config->getPublisher('topic.message.queue.config.02');

        $this->assertSame('topic.message.queue.config.02', $publisher->getTopic(), 'Incorrect topic name');
        $this->assertFalse($publisher->isDisabled(), 'Incorrect publisher state');

        /** @var \Magento\Framework\MessageQueue\Publisher\Config\PublisherConnectionInterface $connection */
        $connection = $publisher->getConnection();
        $this->assertSame('amqp', $connection->getName(), 'Incorrect default connection name');
        $this->assertSame('magento', $connection->getExchange(), 'Incorrect default exchange name');
        $this->assertFalse($connection->isDisabled(), 'Incorrect connection status');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Publisher 'topic.message.queue.config.03' is not declared.
     */
    public function testGetDisabledPublisherThrowsException()
    {
        /** @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Publisher\ConfigInterface::class);
        $config->getPublisher('topic.message.queue.config.03');
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher;

use Magento\Framework\MessageQueue\Publisher\Config\PublisherConnectionInterface;

/**
 * Test access to publisher configuration declared in deprecated queue.xml configs using Publisher\ConfigInterface.
 *
 * @magentoCache config disabled
 */
class DeprecatedConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function testGetPublisher()
    {
        /** @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Publisher\ConfigInterface::class);
        $publisher = $config->getPublisher('deprecated.config.async.string.topic');
        $this->assertSame('deprecated.config.async.string.topic', $publisher->getTopic());
        $this->assertSame(false, $publisher->isDisabled());

        $connection = $publisher->getConnection();
        $this->assertSame('amqp', $connection->getName());
        $this->assertSame('magento', $connection->getExchange());
        $this->assertSame(false, $connection->isDisabled());
    }

    public function testGetPublisherCustomConnection()
    {
        /** @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Publisher\ConfigInterface::class);
        $publisher = $config->getPublisher('deprecated.config.sync.bool.topic');
        $this->assertSame('deprecated.config.sync.bool.topic', $publisher->getTopic());
        $this->assertSame(false, $publisher->isDisabled());

        $connection = $publisher->getConnection();
        $this->assertSame('amqp', $connection->getName());
        $this->assertSame('customExchange', $connection->getExchange());
        $this->assertSame(false, $connection->isDisabled());
    }

    public function testGetOverlapWithQueueConfig()
    {
        /** @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Publisher\ConfigInterface::class);
        $publisher = $config->getPublisher('overlapping.topic.declaration');
        $this->assertSame('overlapping.topic.declaration', $publisher->getTopic());
        $this->assertSame(false, $publisher->isDisabled());

        $connection = $publisher->getConnection();
        $this->assertSame('amqp', $connection->getName());
        $this->assertSame('magento', $connection->getExchange());
        $this->assertSame(false, $connection->isDisabled());
    }
}

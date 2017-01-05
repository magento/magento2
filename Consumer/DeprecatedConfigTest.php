<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer;

use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\Handler\Iterator as HandlerIterator;

/**
 * Test access to consumer configuration declared in deprecated queue.xml configs using Consumer\ConfigInterface.
 *
 * @magentoCache config disabled
 */
class DeprecatedConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function testGetConsumerMultipleHandlersFromCommunicationConfig()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);
        $consumer = $config->getConsumer('deprecatedConfigAsyncBoolConsumer');

        $this->assertEquals('deprecatedConfigAsyncBoolConsumer', $consumer->getName());
        $this->assertEquals('deprecated.config.queue.2', $consumer->getQueue());
        $this->assertEquals('db', $consumer->getConnection());
        $this->assertEquals(\Magento\Framework\MessageQueue\ConsumerInterface::class, $consumer->getConsumerInstance());
        $this->assertEquals(null, $consumer->getMaxMessages());

        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(2, $handlers);
        $this->assertEquals('methodWithBoolParam', $handlers[0]->getMethod());
        $this->assertEquals(\Magento\TestModuleMessageQueueConfiguration\AsyncHandler::class, $handlers[0]->getType());
        $this->assertEquals('methodWithMixedParam', $handlers[1]->getMethod());
        $this->assertEquals(\Magento\TestModuleMessageQueueConfiguration\AsyncHandler::class, $handlers[1]->getType());
    }

    public function testGetConsumerCustomHandler()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);
        $consumer = $config->getConsumer('deprecatedConfigAsyncMixedConsumer');

        $this->assertEquals('deprecatedConfigAsyncMixedConsumer', $consumer->getName());
        $this->assertEquals('deprecated.config.queue.3', $consumer->getQueue());
        $this->assertEquals('amqp', $consumer->getConnection());
        $this->assertEquals(\Magento\Framework\MessageQueue\ConsumerInterface::class, $consumer->getConsumerInstance());
        $this->assertEquals(null, $consumer->getMaxMessages());

        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(1, $handlers);
        $this->assertEquals('methodWithMixedParam', $handlers[0]->getMethod());
        $this->assertEquals(\Magento\TestModuleMessageQueueConfiguration\AsyncHandler::class, $handlers[0]->getType());
    }

    public function testGetConsumerCustomConnectionSync()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);
        $consumer = $config->getConsumer('deprecatedConfigSyncBoolConsumer');

        $this->assertEquals('deprecatedConfigSyncBoolConsumer', $consumer->getName());
        $this->assertEquals('deprecated.config.queue.4', $consumer->getQueue());
        $this->assertEquals('amqp', $consumer->getConnection());
        $this->assertEquals(\Magento\Framework\MessageQueue\ConsumerInterface::class, $consumer->getConsumerInstance());
        $this->assertEquals(null, $consumer->getMaxMessages());

        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(1, $handlers);
        $this->assertEquals('methodWithBoolParam', $handlers[0]->getMethod());
        $this->assertEquals(\Magento\TestModuleMessageQueueConfiguration\SyncHandler::class, $handlers[0]->getType());
    }

    public function testGetConsumerCustomConsumerAndMaxMessages()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);
        $consumer = $config->getConsumer('deprecatedConfigAsyncStringConsumer');

        $this->assertEquals('deprecatedConfigAsyncStringConsumer', $consumer->getName());
        $this->assertEquals('deprecated.config.queue.1', $consumer->getQueue());
        $this->assertEquals('amqp', $consumer->getConnection());
        $this->assertEquals(\Magento\Framework\MessageQueue\BatchConsumer::class, $consumer->getConsumerInstance());
        $this->assertEquals(200, $consumer->getMaxMessages());

        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(0, $handlers);
    }

    public function testGetOverlapWithQueueConfig()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);
        $consumer = $config->getConsumer('overlappingConsumerDeclaration');

        $this->assertEquals('overlappingConsumerDeclaration', $consumer->getName());
        $this->assertEquals('consumer.config.queue', $consumer->getQueue());
        $this->assertEquals('amqp', $consumer->getConnection());
        $this->assertEquals(\Magento\Framework\MessageQueue\ConsumerInterface::class, $consumer->getConsumerInstance());
        $this->assertEquals(null, $consumer->getMaxMessages());

        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(0, $handlers);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer;

use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\Handler\Iterator as HandlerIterator;

/**
 * Test access to consumer configuration declared in deprecated queue.xml configs using Consumer\ConfigInterface.
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

    public function testGetConsumerMultipleHandlersFromCommunicationConfig()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);
        $consumer = $config->getConsumer('deprecatedConfigAsyncBoolConsumer');

        $this->assertSame('deprecatedConfigAsyncBoolConsumer', $consumer->getName());
        $this->assertSame('deprecated.config.queue.2', $consumer->getQueue());
        $this->assertSame('db', $consumer->getConnection());
        $this->assertSame(\Magento\Framework\MessageQueue\ConsumerInterface::class, $consumer->getConsumerInstance());
        $this->assertSame(null, $consumer->getMaxMessages());

        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(2, $handlers);
        $this->assertSame('methodWithBoolParam', $handlers[0]->getMethod());
        $this->assertSame(\Magento\TestModuleMessageQueueConfiguration\AsyncHandler::class, $handlers[0]->getType());
        $this->assertSame('methodWithMixedParam', $handlers[1]->getMethod());
        $this->assertSame(\Magento\TestModuleMessageQueueConfiguration\AsyncHandler::class, $handlers[1]->getType());
    }

    public function testGetConsumerCustomHandler()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);
        $consumer = $config->getConsumer('deprecatedConfigAsyncMixedConsumer');

        $this->assertSame('deprecatedConfigAsyncMixedConsumer', $consumer->getName());
        $this->assertSame('deprecated.config.queue.3', $consumer->getQueue());
        $this->assertSame('amqp', $consumer->getConnection());
        $this->assertSame(\Magento\Framework\MessageQueue\ConsumerInterface::class, $consumer->getConsumerInstance());
        $this->assertSame(null, $consumer->getMaxMessages());

        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(1, $handlers);
        $this->assertSame('methodWithMixedParam', $handlers[0]->getMethod());
        $this->assertSame(\Magento\TestModuleMessageQueueConfiguration\AsyncHandler::class, $handlers[0]->getType());
    }

    public function testGetConsumerCustomConnectionSync()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);
        $consumer = $config->getConsumer('deprecatedConfigSyncBoolConsumer');

        $this->assertSame('deprecatedConfigSyncBoolConsumer', $consumer->getName());
        $this->assertSame('deprecated.config.queue.4', $consumer->getQueue());
        $this->assertSame('amqp', $consumer->getConnection());
        $this->assertSame(\Magento\Framework\MessageQueue\ConsumerInterface::class, $consumer->getConsumerInstance());
        $this->assertSame(null, $consumer->getMaxMessages());

        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(1, $handlers);
        $this->assertSame('methodWithBoolParam', $handlers[0]->getMethod());
        $this->assertSame(\Magento\TestModuleMessageQueueConfiguration\SyncHandler::class, $handlers[0]->getType());
    }

    public function testGetConsumerCustomConsumerAndMaxMessages()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);
        $consumer = $config->getConsumer('deprecatedConfigAsyncStringConsumer');

        $this->assertSame('deprecatedConfigAsyncStringConsumer', $consumer->getName());
        $this->assertSame('deprecated.config.queue.1', $consumer->getQueue());
        $this->assertSame('amqp', $consumer->getConnection());
        $this->assertSame(\Magento\Framework\MessageQueue\BatchConsumer::class, $consumer->getConsumerInstance());
        $this->assertSame(200, $consumer->getMaxMessages());

        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(0, $handlers);
    }

    public function testGetOverlapWithQueueConfig()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);
        $consumer = $config->getConsumer('overlappingConsumerDeclaration');

        $this->assertSame('overlappingConsumerDeclaration', $consumer->getName());
        $this->assertSame('consumer.config.queue', $consumer->getQueue());
        $this->assertSame('amqp', $consumer->getConnection());
        $this->assertSame(\Magento\Framework\MessageQueue\ConsumerInterface::class, $consumer->getConsumerInstance());
        $this->assertSame(null, $consumer->getMaxMessages());

        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(0, $handlers);
    }
}

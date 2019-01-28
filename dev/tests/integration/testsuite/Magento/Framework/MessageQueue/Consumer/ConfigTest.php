<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer;

use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\Handler\Iterator as HandlerIterator;

/**
 * Test of queue consumer configuration reading and parsing.
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

    public function testGetConsumers()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);

        $consumers = $config->getConsumers();
        $consumer = $config->getConsumer('consumer1');

        $this->assertSame(
            $consumer,
            $consumers['consumer1'],
            'Consumers received from collection and via getter must be the same'
        );

        $this->assertSame('consumer1', $consumer->getName());
        $this->assertSame('queue1', $consumer->getQueue());
        $this->assertSame('amqp', $consumer->getConnection());
        $this->assertSame(\Magento\Framework\MessageQueue\BatchConsumer::class, $consumer->getConsumerInstance());
        $this->assertSame('100', $consumer->getMaxMessages());
        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(1, $handlers);
        $this->assertSame('handlerMethodOne', $handlers[0]->getMethod());
        $this->assertSame('Magento\TestModuleMessageQueueConfiguration\HandlerOne', $handlers[0]->getType());
    }

    public function testGetConsumerWithDefaultValues()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);

        $consumer = $config->getConsumer('consumer5');

        $this->assertSame('consumer5', $consumer->getName());
        $this->assertSame('queue5', $consumer->getQueue());
        $this->assertSame('amqp', $consumer->getConnection());
        $this->assertSame(\Magento\Framework\MessageQueue\ConsumerInterface::class, $consumer->getConsumerInstance());
        $this->assertSame(null, $consumer->getMaxMessages());
        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(0, $handlers);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Consumer 'undeclaredConsumer' is not declared.
     */
    public function testGetUndeclaredConsumer()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);
        $config->getConsumer('undeclaredConsumer');
    }
}

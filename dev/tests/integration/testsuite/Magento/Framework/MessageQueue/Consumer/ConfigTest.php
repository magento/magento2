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

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function testGetConsumers()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);

        $consumers = $config->getConsumers();
        $consumer = $config->getConsumer('consumer1');

        $this->assertEquals(
            $consumer,
            $consumers['consumer1'],
            'Consumers received from collection and via getter must be the same'
        );

        $this->assertEquals('consumer1', $consumer->getName());
        $this->assertEquals('queue1', $consumer->getQueue());
        $this->assertEquals('amqp', $consumer->getConnection());
        $this->assertEquals(\Magento\Framework\MessageQueue\BatchConsumer::class, $consumer->getConsumerInstance());
        $this->assertEquals('100', $consumer->getMaxMessages());
        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(1, $handlers);
        $this->assertEquals('handlerMethodOne', $handlers[0]->getMethod());
        $this->assertEquals('Magento\TestModuleMessageQueueConfiguration\HandlerOne', $handlers[0]->getType());
    }

    public function testGetConsumerWithDefaultValues()
    {
        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);

        $consumer = $config->getConsumer('consumer5');

        $this->assertEquals('consumer5', $consumer->getName());
        $this->assertEquals('queue5', $consumer->getQueue());
        $this->assertEquals('amqp', $consumer->getConnection());
        $this->assertEquals(\Magento\Framework\MessageQueue\ConsumerInterface::class, $consumer->getConsumerInstance());
        $this->assertNull($consumer->getMaxMessages());
        $handlers = $consumer->getHandlers();
        $this->assertInstanceOf(HandlerIterator::class, $handlers);
        $this->assertCount(0, $handlers);
    }

    /**
     */
    public function testGetUndeclaredConsumer()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Consumer \'undeclaredConsumer\' is not declared.');

        /** @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class);
        $config->getConsumer('undeclaredConsumer');
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\Binding\Iterator as BindingIterator;

/**
 * Test access to topology configuration declared in deprecated queue.xml configs using Topology\ConfigInterface.
 *
 * @magentoCache config disabled
 */
class DeprecatedConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function testGetTopology()
    {
        /** @var \Magento\Framework\MessageQueue\Topology\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Topology\ConfigInterface::class);
        $topology = $config->getExchange('deprecatedExchange', 'db');
        $this->assertEquals('deprecatedExchange', $topology->getName());
        $this->assertEquals('topic', $topology->getType());
        $this->assertEquals('db', $topology->getConnection());
        $this->assertTrue($topology->isDurable());
        $this->assertFalse($topology->isAutoDelete());
        $this->assertFalse($topology->isInternal());

        $arguments = $topology->getArguments();
        $this->assertIsArray($arguments);
        $this->assertCount(0, $arguments);

        // Verify bindings
        $bindings = $topology->getBindings();
        $this->assertInstanceOf(BindingIterator::class, $bindings);
        $this->assertCount(1, $bindings);

        $bindingId = 'queue--deprecated.config.queue.2--deprecated.config.async.bool.topic';
        $this->assertArrayHasKey($bindingId, $bindings);
        $binding = $bindings[$bindingId];

        $this->assertEquals('queue', $binding->getDestinationType());
        $this->assertEquals('deprecated.config.queue.2', $binding->getDestination());
        $this->assertFalse($binding->isDisabled());
        $this->assertEquals('deprecated.config.async.bool.topic', $binding->getTopic());
        $arguments = $binding->getArguments();
        $this->assertIsArray($arguments);
        $this->assertCount(0, $arguments);
    }

    public function testGetTopologyOverlapWithQueueConfig()
    {
        /** @var \Magento\Framework\MessageQueue\Topology\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Topology\ConfigInterface::class);
        $topology = $config->getExchange('overlappingDeprecatedExchange', 'amqp');
        $this->assertEquals('overlappingDeprecatedExchange', $topology->getName());
        $this->assertEquals('topic', $topology->getType());
        $this->assertEquals('amqp', $topology->getConnection());
        $this->assertTrue($topology->isDurable());
        $this->assertFalse($topology->isAutoDelete());
        $this->assertFalse($topology->isInternal());

        $arguments = $topology->getArguments();
        $this->assertIsArray($arguments);
        $this->assertCount(0, $arguments);

        // Verify bindings
        $bindings = $topology->getBindings();
        $this->assertInstanceOf(BindingIterator::class, $bindings);
        $this->assertCount(3, $bindings);

        // Note that connection was changed for this binding during merge with topology config
        // since we do not support exchanges with the same names on different connections
        $bindingId = 'queue--consumer.config.queue--overlapping.topic.declaration';
        $this->assertArrayHasKey($bindingId, $bindings);
        $binding = $bindings[$bindingId];
        $this->assertEquals('queue', $binding->getDestinationType());
        $this->assertEquals('consumer.config.queue', $binding->getDestination());
        $this->assertFalse($binding->isDisabled());
        $this->assertEquals('overlapping.topic.declaration', $binding->getTopic());
        $arguments = $binding->getArguments();
        $this->assertIsArray($arguments);
        $this->assertCount(0, $arguments);

        $bindingId = 'binding1';
        $this->assertArrayHasKey($bindingId, $bindings);
        $binding = $bindings[$bindingId];
        $this->assertEquals('queue', $binding->getDestinationType());
        $this->assertEquals('topology.config.queue', $binding->getDestination());
        $this->assertFalse($binding->isDisabled());
        $this->assertEquals('overlapping.topic.declaration', $binding->getTopic());
        $arguments = $binding->getArguments();
        $this->assertIsArray($arguments);
        $this->assertCount(0, $arguments);

        $bindingId = 'binding2';
        $this->assertArrayHasKey($bindingId, $bindings);
        $binding = $bindings[$bindingId];
        $this->assertEquals('queue', $binding->getDestinationType());
        $this->assertEquals('topology.config.queue', $binding->getDestination());
        $this->assertFalse($binding->isDisabled());
        $this->assertEquals('deprecated.config.async.string.topic', $binding->getTopic());
        $arguments = $binding->getArguments();
        $this->assertIsArray($arguments);
        $this->assertCount(0, $arguments);
    }
}

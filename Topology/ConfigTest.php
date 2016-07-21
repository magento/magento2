<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;

/**
 * Test of queue topology configuration reading and parsing.
 *
 * @magentoCache config disabled
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function testGetExchangeByName()
    {
        /** @var \Magento\Framework\MessageQueue\Topology\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Topology\ConfigInterface::class);
        $exchange = $config->getExchange('magento-topic-based-exchange1');
        $this->assertEquals('magento-topic-based-exchange1', $exchange->getName());
        $this->assertEquals('topic', $exchange->getType());
        $this->assertEquals('customConnection', $exchange->getConnection());

        /** @var BindingInterface $binding */
        $binding = current($exchange->getBindings());
        $this->assertEquals('topicBasedRouting1', $binding->getId());
        $this->assertEquals('anotherTopic1', $binding->getTopic());
        $this->assertEquals('queue', $binding->getDestinationType());
        $this->assertEquals('topic-queue1', $binding->getDestination());
    }

    public function testGetExchangeByNameWithDefaultValues()
    {
        /** @var \Magento\Framework\MessageQueue\Topology\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Topology\ConfigInterface::class);
        $exchange = $config->getExchange('magento-topic-based-exchange2');
        $this->assertEquals('magento-topic-based-exchange2', $exchange->getName());
        $this->assertEquals('topic', $exchange->getType());
        $this->assertEquals('amqp', $exchange->getConnection());

        /** @var BindingInterface $binding */
        $binding = current($exchange->getBindings());
        $this->assertEquals('topicBasedRouting2', $binding->getId());
        $this->assertEquals('anotherTopic2', $binding->getTopic());
        $this->assertEquals('queue', $binding->getDestinationType());
        $this->assertEquals('topic-queue2', $binding->getDestination());
    }

    public function testGetAllExchanges()
    {
        /** @var \Magento\Framework\MessageQueue\Topology\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Topology\ConfigInterface::class);
        $exchanges = $config->getExchanges();
        $expectedResults = ['magento-topic-based-exchange1', 'magento-topic-based-exchange2'];
        $actual = [];
        foreach ($exchanges as $exchange) {
            $actual[] = $exchange->getName();
        }
        $this->assertEmpty(array_diff($expectedResults, $actual));
    }
}

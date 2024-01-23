<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;

/**
 * Test of queue topology configuration reading and parsing.
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

    public function testGetExchangeByName()
    {
        /** @var \Magento\Framework\MessageQueue\Topology\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Topology\ConfigInterface::class);
        $exchange = $config->getExchange('magento-topic-based-exchange1', 'amqp');
        $this->assertEquals('magento-topic-based-exchange1', $exchange->getName());
        $this->assertEquals('topic', $exchange->getType());
        $this->assertEquals('amqp', $exchange->getConnection());
        $exchangeArguments = $exchange->getArguments();
        $expectedArguments = ['alternate-exchange' => 'magento-log-exchange'];
        $this->assertEquals($expectedArguments, $exchangeArguments);

        /** @var BindingInterface $binding */
        $binding = $exchange->getBindings()->current();
        $this->assertEquals('queue--topic-queue1--anotherTopic1', $binding->getId());
        $this->assertEquals('anotherTopic1', $binding->getTopic());
        $this->assertEquals('queue', $binding->getDestinationType());
        $this->assertEquals('topic-queue1', $binding->getDestination());
        $bindingArguments = $binding->getArguments();
        $expectedArguments = ['argument1' => 'value'];
        $this->assertEquals($expectedArguments, $bindingArguments);
    }

    public function testGetExchangeByNameWithDefaultValues()
    {
        /** @var \Magento\Framework\MessageQueue\Topology\ConfigInterface $config */
        $config = $this->objectManager->create(\Magento\Framework\MessageQueue\Topology\ConfigInterface::class);
        $exchange = $config->getExchange('magento-topic-based-exchange2', 'amqp');
        $this->assertEquals('magento-topic-based-exchange2', $exchange->getName());
        $this->assertEquals('topic', $exchange->getType());
        $this->assertEquals('amqp', $exchange->getConnection());
        $exchangeArguments = $exchange->getArguments();
        $expectedArguments = [
            'alternate-exchange' => 'magento-log-exchange',
            'arrayValue' => [
                'element01' => '10',
                'element02' => '20',
            ]
        ];
        $this->assertEquals($expectedArguments, $exchangeArguments);

        /** @var BindingInterface $binding */
        $binding = $exchange->getBindings()->current();
        $this->assertEquals('queue--topic-queue2--anotherTopic2', $binding->getId());
        $this->assertEquals('anotherTopic2', $binding->getTopic());
        $this->assertEquals('queue', $binding->getDestinationType());
        $this->assertEquals('topic-queue2', $binding->getDestination());
        $bindingArguments = $binding->getArguments();
        $expectedArguments = ['argument1' => 'value', 'argument2' => true, 'argument3' => 150];
        $this->assertEquals($expectedArguments, $bindingArguments);
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

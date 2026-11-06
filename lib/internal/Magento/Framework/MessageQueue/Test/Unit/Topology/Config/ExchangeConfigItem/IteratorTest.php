<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Topology\Config\ExchangeConfigItem;

use Magento\Framework\MessageQueue\Topology\Config\Data;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\Binding\Iterator as BindingIterator;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\Binding\IteratorFactory as BindingIteratorFactory;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItemFactory;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\Iterator;
use PHPUnit\Framework\TestCase;

class IteratorTest extends TestCase
{
    public function testIteratesExchangesOnly(): void
    {
        $configData = $this->createPartialMock(Data::class, ['get']);
        $configData->method('get')->willReturn(
            [
                'queue--some.queue--amqp' => [
                    'name' => 'some.queue',
                    'type' => 'queue',
                    'connection' => 'amqp',
                    'durable' => true,
                    'autoDelete' => false,
                    'arguments' => [],
                ],
                'some.queue--amqp' => [
                    'name' => 'some.queue',
                    'type' => 'topic',
                    'connection' => 'amqp',
                    'durable' => true,
                    'autoDelete' => false,
                    'internal' => false,
                    'bindings' => [],
                    'arguments' => [],
                ],
            ]
        );
        $bindingIteratorFactory = $this->createMock(BindingIteratorFactory::class);
        $bindingIteratorFactory->method('create')->willReturn($this->createMock(BindingIterator::class));
        $itemFactory = $this->createMock(ExchangeConfigItemFactory::class);
        $itemFactory->method('create')->willReturn(new ExchangeConfigItem($bindingIteratorFactory));

        $iterator = new Iterator($configData, $itemFactory);

        $exchanges = [];
        foreach ($iterator as $key => $exchange) {
            $exchanges[$key] = $exchange->getType();
        }
        $this->assertSame(['some.queue--amqp' => 'topic'], $exchanges);
        $this->assertFalse(isset($iterator['queue--some.queue--amqp']));
    }
}

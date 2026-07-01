<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItemInterface;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConnectionInterface;
use Magento\Framework\MessageQueue\QueueResolver;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItemInterface;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as TopologyConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QueueResolverTest extends TestCase
{
    /**
     * @var QueueResolver
     */
    private QueueResolver $queueResolver;

    /**
     * @var PublisherConfig|MockObject
     */
    private PublisherConfig|MockObject $publisherConfig;

    /**
     * @var PublisherConfigItemInterface|MockObject
     */
    private PublisherConfigItemInterface|MockObject $publisher;

    /**
     * @var ExchangeConfigItemInterface|MockObject
     */
    private ExchangeConfigItemInterface|MockObject $exchange;

    protected function setUp(): void
    {
        $this->publisherConfig = $this->createMock(PublisherConfig::class);
        $topologyConfig = $this->createMock(TopologyConfig::class);
        $this->queueResolver = new QueueResolver($this->publisherConfig, $topologyConfig);

        $publisherConnection = $this->createMock(PublisherConnectionInterface::class);
        $publisherConnection->method('getName')->willReturn('name');
        $publisherConnection->method('getExchange')->willReturn('exchange');
        $this->publisher = $this->createMock(PublisherConfigItemInterface::class);
        $this->publisher->method('getConnection')->willReturn($publisherConnection);

        $this->exchange = $this->createMock(ExchangeConfigItemInterface::class);
        $topologyConfig->method('getExchange')->with('exchange', 'name')->willReturn($this->exchange);
    }

    #[DataProvider('topicsDataProvider')]
    public function testGetByTopic(string $topic, string $routingKey, string $destination, string $queue): void
    {
        $this->publisherConfig->expects(self::once())
            ->method('getPublisher')
            ->with($topic)
            ->willReturn($this->publisher);
        $binding = $this->createMock(BindingInterface::class);
        $binding->method('getTopic')->willReturn($routingKey);
        $binding->method('getDestination')->willReturn($destination);
        $this->exchange->expects(self::once())
            ->method('getBindings')
            ->willReturn([$binding]);

        self::assertEquals($queue, $this->queueResolver->getByTopic($topic));
    }

    public static function topicsDataProvider(): array
    {
        return [
            ['a.b.c', 'a.b.c', 'a_b_c', 'a_b_c'],
            ['a.b.c', 'a.b.*', 'a_b_c', 'a_b_c'],
            ['a.b.c', 'a.b.#', 'a_b_c', 'a_b_c'],
            ['a.b.', 'a.b.#', 'a_b_c', 'a_b_c'],
            ['a.b.c.d', 'a.b.#', 'a_b_c', 'a_b_c'],
            ['a.e.c', 'a.b.*', 'a_b_c', 'a.e.c'],
            ['a.e.c', 'a.b.#', 'a_b_c', 'a.e.c'],
        ];
    }
}

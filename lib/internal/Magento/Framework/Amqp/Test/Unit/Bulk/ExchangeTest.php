<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Amqp\Test\Unit\Bulk;

use Magento\Framework\Amqp\Config;
use Magento\Framework\Amqp\Exchange;
use Magento\Framework\Communication\ConfigInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItemInterface;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConnectionInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Exchange model.
 */
class ExchangeTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $amqpConfig;

    /**
     * @var ConfigInterface|MockObject
     */
    private $communicationConfig;

    /**
     * @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface|MockObject
     */
    private $publisherConfig;

    /**
     * @var Exchange|MockObject
     */
    private $exchange;

    /**
     * @var \Magento\Framework\Amqp\Bulk\Exchange
     */
    private $bulkExchange;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->amqpConfig = $this->createMock(Config::class);
        $this->communicationConfig = $this->createMock(ConfigInterface::class);
        $this->publisherConfig = $this->createMock(\Magento\Framework\MessageQueue\Publisher\ConfigInterface::class);
        $this->exchange = $this->createMock(Exchange::class);

        $objectManager = new ObjectManager($this);
        $this->bulkExchange = $objectManager->getObject(
            \Magento\Framework\Amqp\Bulk\Exchange::class,
            [
                'amqpConfig' => $this->amqpConfig,
                'communicationConfig' => $this->communicationConfig,
                'publisherConfig' => $this->publisherConfig,
                'exchange' => $this->exchange,
            ]
        );
    }

    /**
     * Test for enqueue method.
     *
     * @return void
     */
    public function testEnqueue()
    {
        $topicName = 'topic.name';
        $exchangeName = 'exchangeName';
        $envelopeBody = 'envelopeBody';
        $envelopeProperties = ['property_key_1' => 'property_value_1'];
        $topicData = [
            ConfigInterface::TOPIC_IS_SYNCHRONOUS => false
        ];
        $this->communicationConfig->expects($this->once())
            ->method('getTopic')->with($topicName)->willReturn($topicData);
        $channel = $this->getMockBuilder(AMQPChannel::class)
            ->onlyMethods(['batch_basic_publish', 'publish_batch'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->amqpConfig->expects($this->once())->method('getChannel')->willReturn($channel);
        $publisher = $this->createMock(PublisherConfigItemInterface::class);
        $this->publisherConfig->expects($this->once())
            ->method('getPublisher')->with($topicName)->willReturn($publisher);
        $connection = $this->createMock(PublisherConnectionInterface::class);
        $publisher->expects($this->once())->method('getConnection')->with()->willReturn($connection);
        $connection->expects($this->once())->method('getExchange')->with()->willReturn($exchangeName);
        $envelope = $this->createMock(EnvelopeInterface::class);
        $envelope->expects($this->once())->method('getBody')->willReturn($envelopeBody);
        $envelope->expects($this->once())->method('getProperties')->willReturn($envelopeProperties);
        $channel->expects($this->once())->method('batch_basic_publish')
            ->with($this->isInstanceOf(AMQPMessage::class), $exchangeName, $topicName);
        $channel->expects($this->once())->method('publish_batch');
        $this->assertNull($this->bulkExchange->enqueue($topicName, [$envelope]));
    }

    /**
     * Test for enqueue method with synchronous topic.
     *
     * @return void
     */
    public function testEnqueueWithSynchronousTopic()
    {
        $topicName = 'topic.name';
        $response = 'responseBody';
        $topicData = [
            ConfigInterface::TOPIC_IS_SYNCHRONOUS => true
        ];
        $this->communicationConfig->expects($this->once())
            ->method('getTopic')->with($topicName)->willReturn($topicData);
        $envelope = $this->createMock(EnvelopeInterface::class);
        $this->exchange->expects($this->once())->method('enqueue')->with($topicName, $envelope)->willReturn($response);
        $this->assertEquals([$response], $this->bulkExchange->enqueue($topicName, [$envelope]));
    }
}

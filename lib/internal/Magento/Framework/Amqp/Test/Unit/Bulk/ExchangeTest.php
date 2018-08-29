<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Test\Unit\Bulk;

/**
 * Unit test for Exchange model.
 */
class ExchangeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Amqp\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $amqpConfig;

    /**
     * @var \Magento\Framework\Communication\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $communicationConfig;

    /**
     * @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $publisherConfig;

    /**
     * @var \Magento\Framework\Amqp\Exchange|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
    {
        $this->amqpConfig = $this->getMockBuilder(\Magento\Framework\Amqp\Config::class)
            ->disableOriginalConstructor()->getMock();
        $this->communicationConfig = $this->getMockBuilder(\Magento\Framework\Communication\ConfigInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->publisherConfig = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\Publisher\ConfigInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->exchange = $this->getMockBuilder(\Magento\Framework\Amqp\Exchange::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
            \Magento\Framework\Communication\ConfigInterface::TOPIC_IS_SYNCHRONOUS => false
        ];
        $this->communicationConfig->expects($this->once())
            ->method('getTopic')->with($topicName)->willReturn($topicData);
        $channel = $this->getMockBuilder(\AMQPChannel::class)
            ->setMethods(['batch_basic_publish', 'publish_batch'])
            ->disableOriginalConstructor()->getMock();
        $this->amqpConfig->expects($this->once())->method('getChannel')->willReturn($channel);
        $publisher = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->publisherConfig->expects($this->once())
            ->method('getPublisher')->with($topicName)->willReturn($publisher);
        $connection = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\Publisher\Config\PublisherConnectionInterface::class)
            ->disableOriginalConstructor()->getMock();
        $publisher->expects($this->once())->method('getConnection')->with()->willReturn($connection);
        $connection->expects($this->once())->method('getExchange')->with()->willReturn($exchangeName);
        $envelope = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableOriginalConstructor()->getMock();
        $envelope->expects($this->once())->method('getBody')->willReturn($envelopeBody);
        $envelope->expects($this->once())->method('getProperties')->willReturn($envelopeProperties);
        $channel->expects($this->once())->method('batch_basic_publish')
            ->with($this->isInstanceOf(\PhpAmqpLib\Message\AMQPMessage::class), $exchangeName, $topicName);
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
            \Magento\Framework\Communication\ConfigInterface::TOPIC_IS_SYNCHRONOUS => true
        ];
        $this->communicationConfig->expects($this->once())
            ->method('getTopic')->with($topicName)->willReturn($topicData);
        $envelope = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->exchange->expects($this->once())->method('enqueue')->with($topicName, $envelope)->willReturn($response);
        $this->assertEquals([$response], $this->bulkExchange->enqueue($topicName, [$envelope]));
    }
}

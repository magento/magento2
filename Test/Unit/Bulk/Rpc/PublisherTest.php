<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit\Bulk\Rpc;

/**
 * Unit test for Publisher.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PublisherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\Bulk\ExchangeRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $exchangeRepository;

    /**
     * @var \Magento\Framework\MessageQueue\EnvelopeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $envelopeFactory;

    /**
     * @var \Magento\Framework\MessageQueue\MessageEncoder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageEncoder;

    /**
     * @var \Magento\Framework\MessageQueue\MessageValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageValidator;

    /**
     * @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $publisherConfig;

    /**
     * @var \Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseQueueNameBuilder;

    /**
     * @var \Magento\Framework\MessageQueue\MessageIdGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageIdGenerator;

    /**
     * @var \Magento\Framework\MessageQueue\Bulk\Rpc\Publisher
     */
    private $publisher;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->exchangeRepository = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\Bulk\ExchangeRepository::class)
            ->disableOriginalConstructor()->getMock();
        $this->envelopeFactory = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->messageEncoder = $this->getMockBuilder(\Magento\Framework\MessageQueue\MessageEncoder::class)
            ->disableOriginalConstructor()->getMock();
        $this->messageValidator = $this->getMockBuilder(\Magento\Framework\MessageQueue\MessageValidator::class)
            ->disableOriginalConstructor()->getMock();
        $this->publisherConfig = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\Publisher\ConfigInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->responseQueueNameBuilder = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder::class)
            ->disableOriginalConstructor()->getMock();
        $this->messageIdGenerator = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\MessageIdGeneratorInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->publisher = $objectManager->getObject(
            \Magento\Framework\MessageQueue\Bulk\Rpc\Publisher::class,
            [
                'exchangeRepository' => $this->exchangeRepository,
                'envelopeFactory' => $this->envelopeFactory,
                'messageEncoder' => $this->messageEncoder,
                'messageValidator' => $this->messageValidator,
                'publisherConfig' => $this->publisherConfig,
                'responseQueueNameBuilder' => $this->responseQueueNameBuilder,
                'messageIdGenerator' => $this->messageIdGenerator,
            ]
        );
    }

    /**
     * Test for publish method.
     *
     * @return void
     */
    public function testPublish()
    {
        $messageId = 'message-id-001';
        $topicName = 'topic.name';
        $message = 'messageBody';
        $encodedMessage = 'encodedMessageBody';
        $connectionName = 'amqp';
        $queueName = 'queueName';
        $this->responseQueueNameBuilder->expects($this->once())
            ->method('getQueueName')->with($topicName)->willReturn($queueName);
        $this->messageValidator->expects($this->once())->method('validate')->with($topicName, $message);
        $this->messageEncoder->expects($this->once())
            ->method('encode')->with($topicName, $message)->willReturn($encodedMessage);
        $envelope = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->messageIdGenerator->expects($this->once())
            ->method('generate')->with($topicName)->willReturn($messageId);
        $this->envelopeFactory->expects($this->once())->method('create')->with(
            $this->logicalAnd(
                $this->arrayHasKey('body'),
                $this->arrayHasKey('properties'),
                $this->contains($encodedMessage)
            )
        )->willReturn($envelope);
        $publisher = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->publisherConfig->expects($this->once())
            ->method('getPublisher')->with($topicName)->willReturn($publisher);
        $connection = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\Publisher\Config\PublisherConnectionInterface::class)
            ->disableOriginalConstructor()->getMock();
        $publisher->expects($this->once())->method('getConnection')->with()->willReturn($connection);
        $connection->expects($this->once())->method('getName')->with()->willReturn($connectionName);
        $exchange = $this
            ->getMockBuilder(\Magento\Framework\Amqp\Bulk\Exchange::class)
            ->disableOriginalConstructor()->getMock();
        $this->exchangeRepository->expects($this->once())
            ->method('getByConnectionName')->with($connectionName)->willReturn($exchange);
        $exchange->expects($this->once())->method('enqueue')->with($topicName, [$envelope])->willReturn(null);
        $this->assertNull($this->publisher->publish($topicName, [$message]));
    }
}

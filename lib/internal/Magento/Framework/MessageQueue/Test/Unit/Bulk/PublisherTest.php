<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Bulk;

use Magento\Framework\Amqp\Bulk\Exchange;
use Magento\Framework\MessageQueue\Bulk\ExchangeRepository;
use Magento\Framework\MessageQueue\Bulk\Publisher;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageIdGeneratorInterface;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItemInterface;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConnectionInterface;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Publisher.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PublisherTest extends TestCase
{
    /**
     * @var ExchangeRepository|MockObject
     */
    private $exchangeRepository;

    /**
     * @var EnvelopeFactory|MockObject
     */
    private $envelopeFactory;

    /**
     * @var MessageEncoder|MockObject
     */
    private $messageEncoder;

    /**
     * @var MessageValidator|MockObject
     */
    private $messageValidator;

    /**
     * @var ConfigInterface|MockObject
     */
    private $publisherConfig;

    /**
     * @var MessageIdGeneratorInterface|MockObject
     */
    private $messageIdGenerator;

    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->exchangeRepository = $this
            ->getMockBuilder(ExchangeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->envelopeFactory = $this->getMockBuilder(EnvelopeFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageEncoder = $this->getMockBuilder(MessageEncoder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageValidator = $this->getMockBuilder(MessageValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->publisherConfig = $this
            ->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageIdGenerator = $this
            ->getMockBuilder(MessageIdGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->publisher = $objectManager->getObject(
            Publisher::class,
            [
                'exchangeRepository' => $this->exchangeRepository,
                'envelopeFactory' => $this->envelopeFactory,
                'messageEncoder' => $this->messageEncoder,
                'messageValidator' => $this->messageValidator,
                'publisherConfig' => $this->publisherConfig,
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
        $this->messageValidator->expects($this->once())->method('validate')->with($topicName, $message);
        $this->messageEncoder->expects($this->once())
            ->method('encode')->with($topicName, $message)->willReturn($encodedMessage);
        $envelope = $this->getMockBuilder(EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageIdGenerator->expects($this->once())
            ->method('generate')->with($topicName)->willReturn($messageId);
        $this->envelopeFactory->expects($this->once())->method('create')->with(
            [
                'body' => $encodedMessage,
                'properties' => [
                    'message_id' => $messageId,
                    'topic_name' => $topicName
                ]
            ]
        )->willReturn($envelope);
        $publisher = $this
            ->getMockBuilder(PublisherConfigItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->publisherConfig->expects($this->once())
            ->method('getPublisher')->with($topicName)->willReturn($publisher);
        $connection = $this
            ->getMockBuilder(PublisherConnectionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $publisher->expects($this->once())->method('getConnection')->with()->willReturn($connection);
        $connection->expects($this->once())->method('getName')->with()->willReturn($connectionName);
        $exchange = $this
            ->getMockBuilder(Exchange::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->exchangeRepository->expects($this->once())
            ->method('getByConnectionName')->with($connectionName)->willReturn($exchange);
        $exchange->expects($this->once())->method('enqueue')->with($topicName, [$envelope])->willReturn(null);
        $this->assertNull($this->publisher->publish($topicName, [$message]));
    }
}

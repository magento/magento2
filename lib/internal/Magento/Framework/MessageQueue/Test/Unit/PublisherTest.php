<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\Amqp\Config as AmqpConfig;
use Magento\Framework\MessageQueue\Envelope;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\ExchangeRepository;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\Publisher;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItem;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConnection;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MysqlMq\Model\Driver\Exchange;

/**
 * Class PublisherTest @covers \Magento\Framework\MessageQueue\Publisher
 */
class PublisherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test subject.
     *
     * @var Publisher
     */
    private $publisher;

    /**
     * Publisher config mock.
     *
     * @var PublisherConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $publisherConfig;

    /**
     * Amqp config mock.
     *
     * @var AmqpConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $amqpConfig;

    /**
     * Message validator mock.
     *
     * @var MessageValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageValidator;

    /**
     * Message encoder mock.
     *
     * @var MessageEncoder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageEncoder;

    /**
     * Exchange repository mock.
     *
     * @var ExchangeRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $exchangeRepository;

    /**
     * Envelope factory mock.
     *
     * @var EnvelopeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $envelopeFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->publisherConfig = $this->getMockBuilder(PublisherConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->amqpConfig = $this->getMockBuilder(AmqpConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageValidator = $this->getMockBuilder(MessageValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageEncoder = $this->getMockBuilder(MessageEncoder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->exchangeRepository = $this->getMockBuilder(ExchangeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->envelopeFactory = $this->getMockBuilder(EnvelopeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->publisher = $objectManager->getObject(
            Publisher::class,
            [
                'messageValidator' => $this->messageValidator,
                'envelopeFactory' => $this->envelopeFactory,
                'messageEncoder' => $this->messageEncoder,
                'exchangeRepository' => $this->exchangeRepository,
            ]
        );
        $objectManager->setBackwardCompatibleProperty($this->publisher, 'publisherConfig', $this->publisherConfig);
        $objectManager->setBackwardCompatibleProperty($this->publisher, 'amqpConfig', $this->amqpConfig);
    }

    /**
     * @covers \Magento\Framework\MessageQueue\Publisher::publish()
     */
    public function testPublish()
    {
        $topicName = 'tesTopicName';
        $data = ['testData'];
        $encodedData = 'testEncodedData';
        $body = 'testBody';
        $envelope = new Envelope($body);
        $exchange = $this->getMockBuilder(Exchange::class)
            ->disableOriginalConstructor()
            ->getMock();
        $exchange->expects(self::once())
            ->method('enqueue')
            ->with(self::identicalTo($topicName), self::identicalTo($envelope))
            ->willReturn(null);
        $connection = $this->getMockBuilder(PublisherConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects(self::once())
            ->method('getName')
            ->willReturn('amqp');
        $publisher = $this->getMockBuilder(PublisherConfigItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $publisher->expects(self::once())
            ->method('getConnection')
            ->willReturn($connection);
        $this->messageValidator->expects(self::once())
            ->method('validate');
        $this->messageEncoder->expects(self::once())
            ->method('encode')
            ->with(self::identicalTo($topicName), self::identicalTo($data))
            ->willReturn($encodedData);
        $this->envelopeFactory->expects(self::once())
            ->method('create')
            ->willReturn($envelope);
        $this->publisherConfig->expects(self::once())
            ->method('getPublisher')
            ->with($topicName)
            ->willReturn($publisher);
        $this->amqpConfig->expects(self::once())
            ->method('getValue')
            ->with(AmqpConfig::HOST)
            ->willReturn('');
        $this->exchangeRepository->expects(self::once())
            ->method('getByConnectionName')
            ->with('db')
            ->willReturn($exchange);
        self::assertNull($this->publisher->publish($topicName, $data));
    }
}

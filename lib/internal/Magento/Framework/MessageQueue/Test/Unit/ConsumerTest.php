<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\MessageQueue\CallbackInvoker;
use Magento\Framework\MessageQueue\Consumer;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillCompareInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillReadInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for Consumer class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumerTest extends TestCase
{
    /**
     * @var ConsumerConfigurationInterface|MockObject
     */
    private $configuration;

    /**
     * @var MessageEncoder|MockObject
     */
    private $messageEncoder;

    /**
     * @var QueueRepository|MockObject
     */
    private $queueRepository;

    /**
     * @var \Magento\Framework\MessageQueue\CallbackInvoker
     */
    private $callbackInvoker;

    /**
     * @var ConfigInterface|MockObject
     */
    private $consumerConfig;

    /**
     * @var MessageController|MockObject
     */
    private $messageController;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\Communication\ConfigInterface|MockObject
     */
    private $communicationConfig;

    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @var PoisonPillReadInterface|MockObject
     */
    private $poisonPillRead;

    /**
     * @var PoisonPillCompareInterface|MockObject
     */
    private $poisonPillCompare;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->configuration = $this
            ->getMockBuilder(ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageEncoder = $this->getMockBuilder(MessageEncoder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queueRepository = $this->getMockBuilder(QueueRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);

        $objectManager = new ObjectManager($this);
        $this->poisonPillCompare = $this->createMock(PoisonPillCompareInterface::class);
        $this->poisonPillRead = $this->createMock(PoisonPillReadInterface::class);
        //Hard dependency used because CallbackInvoker invokes closure logic defined inside of Customer class.
        $this->callbackInvoker = new CallbackInvoker(
            $this->poisonPillRead,
            $this->poisonPillCompare,
            $this->deploymentConfig
        );
        $this->consumer = $objectManager->getObject(
            Consumer::class,
            [
                'invoker' => $this->callbackInvoker,
                'messageEncoder' => $this->messageEncoder,
                'resource' => $this->resource,
                'configuration' => $this->configuration,
                'logger' => $this->logger,
                'queueRepository' => $this->queueRepository
            ]
        );

        $this->consumerConfig = $this->createMock(ConfigInterface::class);
        $objectManager->setBackwardCompatibleProperty(
            $this->consumer,
            'consumerConfig',
            $this->consumerConfig
        );
        $this->messageController = $this->getMockBuilder(MessageController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->setBackwardCompatibleProperty(
            $this->consumer,
            'messageController',
            $this->messageController
        );
        $this->communicationConfig = $this
            ->createMock(\Magento\Framework\Communication\ConfigInterface::class);
        $objectManager->setBackwardCompatibleProperty(
            $this->consumer,
            'communicationConfig',
            $this->communicationConfig
        );
    }

    /**
     * Test for process method with NotFoundException.
     *
     * @return void
     */
    public function testProcessWithNotFoundException()
    {
        $properties = ['topic_name' => 'topic.name'];
        $topicConfig = ['is_synchronous' => true];
        $numberOfMessages = 1;
        $consumerName = 'consumer.name';
        $exceptionPhrase = new Phrase('Exception successfully thrown');
        $this->poisonPillRead->expects($this->atLeastOnce())->method('getLatestVersion')->willReturn('version-1');
        $this->poisonPillCompare->expects($this->atLeastOnce())->method('isLatestVersion')->willReturn(true);
        $this->deploymentConfig->expects($this->any())->method('get')
            ->with('queue/consumers_wait_for_messages', 1)->willReturn(1);
        $queue = $this->createMock(QueueInterface::class);
        $this->configuration->expects($this->once())->method('getQueue')->willReturn($queue);
        $envelope = $this->createMock(EnvelopeInterface::class);
        $queue->expects($this->atLeastOnce())->method('dequeue')->willReturn($envelope);
        $envelope->expects($this->once())->method('getProperties')->willReturn($properties);
        $this->communicationConfig->expects($this->once())->method('getTopic')->with($properties['topic_name'])
            ->willReturn($topicConfig);
        $this->configuration->expects($this->atLeastOnce())->method('getConsumerName')->willReturn($consumerName);
        $this->messageController->expects($this->once())->method('lock')->with($envelope, $consumerName)
            ->willThrowException(
                new NotFoundException(
                    $exceptionPhrase
                )
            );
        $queue->expects($this->once())->method('acknowledge')->with($envelope);
        $this->logger->expects($this->once())->method('warning')->with($exceptionPhrase->render());

        $this->consumer->process($numberOfMessages);
    }

    /**
     * Test for process method with 'getMaxIdleTime' and 'getSleep' consumer configurations
     *
     * @return void
     */
    public function testProcessWithGetMaxIdleTimeAndGetSleepConsumerConfigurations()
    {
        $numberOfMessages = 1;
        $this->poisonPillRead->expects($this->atLeastOnce())->method('getLatestVersion')->willReturn('version-1');
        $this->poisonPillCompare->expects($this->any())->method('isLatestVersion')->willReturn(true);
        $this->deploymentConfig->expects($this->any())->method('get')
            ->with('queue/consumers_wait_for_messages', 1)->willReturn(1);
        $queue = $this->getMockBuilder(\Magento\Framework\MessageQueue\QueueInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->configuration->expects($this->once())->method('getQueue')->willReturn($queue);
        $queue->expects($this->atMost(2))->method('dequeue')->willReturn(null);
        $this->configuration->expects($this->once())->method('getMaxIdleTime')->willReturn('2');
        $this->configuration->expects($this->once())->method('getSleep')->willReturn('2');
        $this->consumer->process($numberOfMessages);
    }
}

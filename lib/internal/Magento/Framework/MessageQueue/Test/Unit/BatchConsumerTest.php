<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MessageQueue\BatchConsumer;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\MergerFactory;
use Magento\Framework\MessageQueue\MergerInterface;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageLockException;
use Magento\Framework\MessageQueue\MessageProcessorInterface;
use Magento\Framework\MessageQueue\MessageProcessorLoader;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for BatchConsumer class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BatchConsumerTest extends TestCase
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
     * @var MergerFactory|MockObject
     */
    private $mergerFactory;

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
     * @var BatchConsumer
     */
    private $batchConsumer;

    /**
     * @var int
     */
    private $batchSize = 10;

    /**
     * @var MessageProcessorLoader|MockObject
     */
    private $messageProcessorLoader;

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
            ->getMockForAbstractClass();
        $this->messageEncoder = $this->getMockBuilder(MessageEncoder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queueRepository = $this->getMockBuilder(QueueRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mergerFactory = $this->getMockBuilder(MergerFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageProcessorLoader = $this
            ->getMockBuilder(MessageProcessorLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->batchConsumer = $objectManager->getObject(
            BatchConsumer::class,
            [
                'configuration' => $this->configuration,
                'messageEncoder' => $this->messageEncoder,
                'queueRepository' => $this->queueRepository,
                'mergerFactory' => $this->mergerFactory,
                'resource' => $this->resource,
                'batchSize' => $this->batchSize,
                'messageProcessorLoader' => $this->messageProcessorLoader
            ]
        );

        $this->consumerConfig = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager->setBackwardCompatibleProperty(
            $this->batchConsumer,
            'consumerConfig',
            $this->consumerConfig
        );
        $this->messageController = $this->getMockBuilder(MessageController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->setBackwardCompatibleProperty(
            $this->batchConsumer,
            'messageController',
            $this->messageController
        );
    }

    /**
     * Test for process().
     *
     * @return void
     */
    public function testProcess()
    {
        $queueName = 'queue.name';
        $consumerName = 'consumerName';
        $connectionName = 'connection_name';
        $topicName = 'topicName';
        $messageBody = 'messageBody';
        $message = ['message_data'];
        $numberOfMessages = 2;
        $this->configuration->expects($this->once())->method('getQueueName')->willReturn($queueName);
        $this->configuration->expects($this->atLeastOnce())->method('getConsumerName')->willReturn($consumerName);
        $consumerConfigItem = $this
            ->getMockBuilder(ConsumerConfigItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->consumerConfig->expects($this->once())
            ->method('getConsumer')->with($consumerName)->willReturn($consumerConfigItem);
        $consumerConfigItem->expects($this->once())->method('getConnection')->willReturn($connectionName);
        $queue = $this->getMockBuilder(QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->queueRepository->expects($this->once())
            ->method('get')->with($connectionName, $queueName)->willReturn($queue);
        $merger = $this->getMockBuilder(MergerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->mergerFactory->expects($this->once())->method('create')->with($consumerName)->willReturn($merger);
        $envelope = $this->getMockBuilder(EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $queue->expects($this->exactly($numberOfMessages))->method('dequeue')->willReturn($envelope);
        $this->messageController->expects($this->exactly($numberOfMessages))
            ->method('lock')->with($envelope, $consumerName);
        $envelope->expects($this->exactly($numberOfMessages))
            ->method('getProperties')->willReturn(['topic_name' => $topicName]);
        $envelope->expects($this->exactly($numberOfMessages))
            ->method('getBody')->willReturn($messageBody);
        $this->messageEncoder->expects($this->exactly($numberOfMessages))
            ->method('decode')->with($topicName, $messageBody)->willReturn($message);
        $messageProcessor = $this->getMockBuilder(MessageProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageProcessorLoader->expects($this->atLeastOnce())->method('load')->willReturn($messageProcessor);
        $merger->expects($this->once())->method('merge')
            ->with([$topicName => [$message, $message]])->willReturnArgument(0);

        $this->batchConsumer->process($numberOfMessages);
    }

    /**
     * Test for process() with MessageLockException.
     *
     * @return void
     */
    public function testProcessWithMessageLockException()
    {
        $queueName = 'queue.name';
        $consumerName = 'consumerName';
        $connectionName = 'connection_name';
        $numberOfMessages = 2;
        $this->configuration->expects($this->once())->method('getQueueName')->willReturn($queueName);
        $this->configuration->expects($this->atLeastOnce())->method('getConsumerName')->willReturn($consumerName);
        $consumerConfigItem = $this
            ->getMockBuilder(ConsumerConfigItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->consumerConfig->expects($this->once())
            ->method('getConsumer')->with($consumerName)->willReturn($consumerConfigItem);
        $consumerConfigItem->expects($this->once())->method('getConnection')->willReturn($connectionName);
        $queue = $this->getMockBuilder(QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->queueRepository->expects($this->once())
            ->method('get')->with($connectionName, $queueName)->willReturn($queue);
        $merger = $this->getMockBuilder(MergerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->mergerFactory->expects($this->once())->method('create')->with($consumerName)->willReturn($merger);
        $envelope = $this->getMockBuilder(EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $queue->expects($this->exactly($numberOfMessages))->method('dequeue')->willReturn($envelope);
        $exception = new MessageLockException(__('Exception Message'));
        $this->messageController->expects($this->atLeastOnce())
            ->method('lock')->with($envelope, $consumerName)->willThrowException($exception);
        $messageProcessor = $this->getMockBuilder(MessageProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageProcessorLoader->expects($this->atLeastOnce())->method('load')->willReturn($messageProcessor);
        $merger->expects($this->once())->method('merge')->willReturn([]);

        $this->batchConsumer->process($numberOfMessages);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

/**
 * Unit test for BatchConsumer class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BatchConsumerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\ConsumerConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configuration;

    /**
     * @var \Magento\Framework\MessageQueue\MessageEncoder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageEncoder;

    /**
     * @var \Magento\Framework\MessageQueue\QueueRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueRepository;

    /**
     * @var \Magento\Framework\MessageQueue\MergerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mergerFactory;

    /**
     * @var \Magento\Framework\MessageQueue\Consumer\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $consumerConfig;

    /**
     * @var \Magento\Framework\MessageQueue\MessageController|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageController;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\MessageQueue\BatchConsumer
     */
    private $batchConsumer;

    /**
     * @var int
     */
    private $batchSize = 10;

    /**
     * @var \Magento\Framework\MessageQueue\MessageProcessorLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageProcessorLoader;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->configuration = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->messageEncoder = $this->getMockBuilder(\Magento\Framework\MessageQueue\MessageEncoder::class)
            ->disableOriginalConstructor()->getMock();
        $this->queueRepository = $this->getMockBuilder(\Magento\Framework\MessageQueue\QueueRepository::class)
            ->disableOriginalConstructor()->getMock();
        $this->mergerFactory = $this->getMockBuilder(\Magento\Framework\MessageQueue\MergerFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()->getMock();
        $this->messageProcessorLoader = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\MessageProcessorLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->batchConsumer = $objectManager->getObject(
            \Magento\Framework\MessageQueue\BatchConsumer::class,
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

        $this->consumerConfig = $this->getMockBuilder(\Magento\Framework\MessageQueue\Consumer\ConfigInterface::class)
            ->disableOriginalConstructor()->getMock();
        $objectManager->setBackwardCompatibleProperty(
            $this->batchConsumer,
            'consumerConfig',
            $this->consumerConfig
        );
        $this->messageController = $this->getMockBuilder(\Magento\Framework\MessageQueue\MessageController::class)
            ->disableOriginalConstructor()->getMock();
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
            ->getMockBuilder(\Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->consumerConfig->expects($this->once())
            ->method('getConsumer')->with($consumerName)->willReturn($consumerConfigItem);
        $consumerConfigItem->expects($this->once())->method('getConnection')->willReturn($connectionName);
        $queue = $this->getMockBuilder(\Magento\Framework\MessageQueue\QueueInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->queueRepository->expects($this->once())
            ->method('get')->with($connectionName, $queueName)->willReturn($queue);
        $merger = $this->getMockBuilder(\Magento\Framework\MessageQueue\MergerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->mergerFactory->expects($this->once())->method('create')->with($consumerName)->willReturn($merger);
        $envelope = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableOriginalConstructor()->getMock();
        $queue->expects($this->exactly($numberOfMessages))->method('dequeue')->willReturn($envelope);
        $this->messageController->expects($this->exactly($numberOfMessages))
            ->method('lock')->with($envelope, $consumerName);
        $envelope->expects($this->exactly($numberOfMessages))
            ->method('getProperties')->willReturn(['topic_name' => $topicName]);
        $envelope->expects($this->exactly($numberOfMessages))
            ->method('getBody')->willReturn($messageBody);
        $this->messageEncoder->expects($this->exactly($numberOfMessages))
            ->method('decode')->with($topicName, $messageBody)->willReturn($message);
        $messageProcessor = $this->getMockBuilder(\Magento\Framework\MessageQueue\MessageProcessorInterface::class)
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
            ->getMockBuilder(\Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->consumerConfig->expects($this->once())
            ->method('getConsumer')->with($consumerName)->willReturn($consumerConfigItem);
        $consumerConfigItem->expects($this->once())->method('getConnection')->willReturn($connectionName);
        $queue = $this->getMockBuilder(\Magento\Framework\MessageQueue\QueueInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->queueRepository->expects($this->once())
            ->method('get')->with($connectionName, $queueName)->willReturn($queue);
        $merger = $this->getMockBuilder(\Magento\Framework\MessageQueue\MergerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->mergerFactory->expects($this->once())->method('create')->with($consumerName)->willReturn($merger);
        $envelope = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableOriginalConstructor()->getMock();
        $queue->expects($this->exactly($numberOfMessages))->method('dequeue')->willReturn($envelope);
        $exception = new \Magento\Framework\MessageQueue\MessageLockException(__('Exception Message'));
        $this->messageController->expects($this->atLeastOnce())
            ->method('lock')->with($envelope, $consumerName)->willThrowException($exception);
        $messageProcessor = $this->getMockBuilder(\Magento\Framework\MessageQueue\MessageProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageProcessorLoader->expects($this->atLeastOnce())->method('load')->willReturn($messageProcessor);
        $merger->expects($this->once())->method('merge')->willReturn([]);

        $this->batchConsumer->process($numberOfMessages);
    }
}

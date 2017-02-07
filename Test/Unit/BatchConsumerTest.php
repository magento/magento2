<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

/**
 * Unit test for BatchConsumer class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BatchConsumerTest extends \PHPUnit_Framework_TestCase
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
     * Test for process method.
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
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resource->expects($this->atLeastOnce())->method('getConnection')->willReturn($connection);
        $connection->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $this->messageController->expects($this->exactly($numberOfMessages))
            ->method('lock')->with($envelope, $consumerName);
        $queue->expects($this->exactly($numberOfMessages))
            ->method('acknowledge')->with($envelope)->willReturn($envelope);
        $envelope->expects($this->exactly($numberOfMessages))
            ->method('getProperties')->willReturn(['topic_name' => $topicName]);
        $envelope->expects($this->exactly($numberOfMessages))
            ->method('getBody')->willReturn($messageBody);
        $this->messageEncoder->expects($this->exactly($numberOfMessages))
            ->method('decode')->with($topicName, $messageBody)->willReturn($message);
        $merger->expects($this->once())->method('merge')
            ->with([$topicName => [$message, $message]])->willReturnArgument(0);
        $this->configuration->expects($this->exactly($numberOfMessages))->method('getHandlers')->with($topicName)
            ->willReturn([]);
        $connection->expects($this->once())->method('commit')->willReturnSelf();
        $this->batchConsumer->process($numberOfMessages);
    }

    /**
     * Test for process method with ConnectionLostException.
     *
     * @return void
     */
    public function testProcessWithConnectionLostException()
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
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resource->expects($this->atLeastOnce())->method('getConnection')->willReturn($connection);
        $connection->expects($this->once())->method('beginTransaction')->willThrowException(
            new \Magento\Framework\MessageQueue\ConnectionLostException()
        );
        $connection->expects($this->once())->method('rollback')->willReturnSelf();
        $this->batchConsumer->process($numberOfMessages);
    }

    /**
     * Test for process method with exception.
     *
     * @return void
     */
    public function testProcessWithException()
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
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resource->expects($this->atLeastOnce())->method('getConnection')->willReturn($connection);
        $connection->expects($this->once())->method('beginTransaction')->willThrowException(
            new \Exception()
        );
        $connection->expects($this->once())->method('rollback')->willReturnSelf();
        $queue->expects($this->exactly($numberOfMessages))->method('reject');
        $this->batchConsumer->process($numberOfMessages);
    }
}

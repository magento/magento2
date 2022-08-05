<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\MessageProcessor;
use Magento\Framework\MessageQueue\MessageStatusProcessor;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for MessageProcessor.
 */
class MessageProcessorTest extends TestCase
{
    /**
     * @var MessageStatusProcessor|MockObject
     */
    private $messageStatusProcessor;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var MessageProcessor
     */
    private $messageProcessor;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->messageStatusProcessor = $this
            ->getMockBuilder(MessageStatusProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->messageProcessor = $objectManagerHelper->getObject(
            MessageProcessor::class,
            [
                'messageStatusProcessor' => $this->messageStatusProcessor,
                'resource' => $this->resource
            ]
        );
    }

    /**
     * Test for process().
     *
     * @return void
     */
    public function testProcess()
    {
        $topicName = 'topic';
        $messagesToAcknowledge = [];
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connection->expects($this->atLeastOnce())->method('beginTransaction');
        $connection->expects($this->atLeastOnce())->method('commit');
        $this->resource->expects($this->atLeastOnce())->method('getConnection')->willReturn($connection);
        $queue = $this->getMockBuilder(QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration = $this->getMockBuilder(ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration->expects($this->atLeastOnce())->method('getHandlers')->willReturn([]);
        $this->messageStatusProcessor->expects($this->exactly(2))->method('acknowledgeMessages');
        $mergedMessage = $this->getMockBuilder(CustomAttributesDataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $message = $this->getMockBuilder(EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mergedMessages = [
            $topicName => [$mergedMessage]
        ];
        $messages = [
            $topicName => [$message]
        ];

        $this->messageProcessor->process($queue, $configuration, $messages, $messagesToAcknowledge, $mergedMessages);
    }

    /**
     * Test for process() with ConnectionLostException.
     *
     * @return void
     */
    public function testProcessWithConnectionLostException()
    {
        $topicName = 'topic';
        $messagesToAcknowledge = [];
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connection->expects($this->atLeastOnce())->method('beginTransaction');
        $connection->expects($this->never())->method('commit');
        $connection->expects($this->atLeastOnce())->method('rollBack');
        $this->resource->expects($this->atLeastOnce())->method('getConnection')->willReturn($connection);
        $queue = $this->getMockBuilder(QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration = $this->getMockBuilder(ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $exception = new ConnectionLostException('Exception Message');
        $configuration->expects($this->atLeastOnce())->method('getHandlers')->willThrowException($exception);
        $this->messageStatusProcessor->expects($this->once())->method('acknowledgeMessages');
        $mergedMessage = $this->getMockBuilder(CustomAttributesDataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $message = $this->getMockBuilder(EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mergedMessages = [
            $topicName => [$mergedMessage]
        ];
        $messages = [
            $topicName => [$message]
        ];

        $this->messageProcessor->process($queue, $configuration, $messages, $messagesToAcknowledge, $mergedMessages);
    }

    /**
     * Test for process() with Exception.
     *
     * @return void
     */
    public function testProcessWithException()
    {
        $topicName = 'topic';
        $messagesToAcknowledge = [];
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connection->expects($this->atLeastOnce())->method('beginTransaction');
        $connection->expects($this->never())->method('commit');
        $connection->expects($this->atLeastOnce())->method('rollBack');
        $this->resource->expects($this->atLeastOnce())->method('getConnection')->willReturn($connection);
        $queue = $this->getMockBuilder(QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration = $this->getMockBuilder(ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $exception = new \Exception();
        $configuration->expects($this->atLeastOnce())->method('getHandlers')->willThrowException($exception);
        $this->messageStatusProcessor->expects($this->once())->method('acknowledgeMessages');
        $this->messageStatusProcessor->expects($this->atLeastOnce())->method('rejectMessages');
        $mergedMessage = $this->getMockBuilder(CustomAttributesDataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $message = $this->getMockBuilder(EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mergedMessages = [
            $topicName => [$mergedMessage]
        ];
        $messages = [
            $topicName => [$message]
        ];

        $this->messageProcessor->process($queue, $configuration, $messages, $messagesToAcknowledge, $mergedMessages);
    }
}

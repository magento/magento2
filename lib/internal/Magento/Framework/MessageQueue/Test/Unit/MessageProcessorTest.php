<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for MessageProcessor.
 */
class MessageProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\MessageStatusProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageStatusProcessor;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\MessageQueue\MessageProcessor
     */
    private $messageProcessor;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->messageStatusProcessor = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\MessageStatusProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->messageProcessor = $objectManagerHelper->getObject(
            \Magento\Framework\MessageQueue\MessageProcessor::class,
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
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connection->expects($this->atLeastOnce())->method('beginTransaction');
        $connection->expects($this->atLeastOnce())->method('commit');
        $this->resource->expects($this->atLeastOnce())->method('getConnection')->willReturn($connection);
        $queue = $this->getMockBuilder(\Magento\Framework\MessageQueue\QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration->expects($this->atLeastOnce())->method('getHandlers')->willReturn([]);
        $this->messageStatusProcessor->expects($this->exactly(2))->method('acknowledgeMessages');
        $mergedMessage = $this->getMockBuilder(\Magento\Framework\Api\CustomAttributesDataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $message = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
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
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connection->expects($this->atLeastOnce())->method('beginTransaction');
        $connection->expects($this->never())->method('commit');
        $connection->expects($this->atLeastOnce())->method('rollBack');
        $this->resource->expects($this->atLeastOnce())->method('getConnection')->willReturn($connection);
        $queue = $this->getMockBuilder(\Magento\Framework\MessageQueue\QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $exception = new \Magento\Framework\MessageQueue\ConnectionLostException(__('Exception Message'));
        $configuration->expects($this->atLeastOnce())->method('getHandlers')->willThrowException($exception);
        $this->messageStatusProcessor->expects($this->once())->method('acknowledgeMessages');
        $mergedMessage = $this->getMockBuilder(\Magento\Framework\Api\CustomAttributesDataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $message = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
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
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connection->expects($this->atLeastOnce())->method('beginTransaction');
        $connection->expects($this->never())->method('commit');
        $connection->expects($this->atLeastOnce())->method('rollBack');
        $this->resource->expects($this->atLeastOnce())->method('getConnection')->willReturn($connection);
        $queue = $this->getMockBuilder(\Magento\Framework\MessageQueue\QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $exception = new \Exception();
        $configuration->expects($this->atLeastOnce())->method('getHandlers')->willThrowException($exception);
        $this->messageStatusProcessor->expects($this->once())->method('acknowledgeMessages');
        $this->messageStatusProcessor->expects($this->atLeastOnce())->method('rejectMessages');
        $mergedMessage = $this->getMockBuilder(\Magento\Framework\Api\CustomAttributesDataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $message = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
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

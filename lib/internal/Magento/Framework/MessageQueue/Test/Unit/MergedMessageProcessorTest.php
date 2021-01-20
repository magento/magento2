<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for MergedMessageProcessor.
 */
class MergedMessageProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\MessageStatusProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageStatusProcessor;

    /**
     * @var \Magento\Framework\MessageQueue\MergedMessageProcessor
     */
    private $mergedMessageProcessor;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->messageStatusProcessor = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\MessageStatusProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->mergedMessageProcessor = $objectManagerHelper->getObject(
            \Magento\Framework\MessageQueue\MergedMessageProcessor::class,
            [
                'messageStatusProcessor' => $this->messageStatusProcessor
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
        $messageId = 1;
        $messagesToAcknowledge = [];
        $queue = $this->getMockBuilder(\Magento\Framework\MessageQueue\QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration->expects($this->atLeastOnce())->method('getHandlers')->willReturn([]);
        $this->messageStatusProcessor->expects($this->exactly(2))->method('acknowledgeMessages');
        $originalMessage = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mergedMessage = $this->getMockBuilder(\Magento\Framework\MessageQueue\MergedMessageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mergedMessage->expects($this->atLeastOnce())->method('getOriginalMessagesIds')->willReturn([$messageId]);
        $mergedMessages = [
            $topicName => [$mergedMessage]
        ];
        $messages = [$messageId => $originalMessage];

        $this->mergedMessageProcessor->process(
            $queue,
            $configuration,
            $messages,
            $messagesToAcknowledge,
            $mergedMessages
        );
    }

    /**
     * Test for process() with Exception.
     *
     * @return void
     */
    public function testProcessWithException()
    {
        $topicName = 'topic';
        $messageId = 1;
        $messagesToAcknowledge = [];
        $queue = $this->getMockBuilder(\Magento\Framework\MessageQueue\QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageStatusProcessor->expects($this->once())->method('acknowledgeMessages');
        $exception = new \Exception();
        $configuration->expects($this->atLeastOnce())->method('getHandlers')->willThrowException($exception);
        $this->messageStatusProcessor->expects($this->atLeastOnce())->method('rejectMessages');
        $originalMessage = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mergedMessage = $this->getMockBuilder(\Magento\Framework\MessageQueue\MergedMessageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mergedMessages = [
            $topicName => [$mergedMessage]
        ];
        $messages = [$messageId => $originalMessage];

        $this->mergedMessageProcessor->process(
            $queue,
            $configuration,
            $messages,
            $messagesToAcknowledge,
            $mergedMessages
        );
    }
}

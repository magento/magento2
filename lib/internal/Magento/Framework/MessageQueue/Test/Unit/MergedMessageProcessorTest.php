<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\MergedMessageInterface;
use Magento\Framework\MessageQueue\MergedMessageProcessor;
use Magento\Framework\MessageQueue\MessageStatusProcessor;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for MergedMessageProcessor.
 */
class MergedMessageProcessorTest extends TestCase
{
    /**
     * @var MessageStatusProcessor|MockObject
     */
    private $messageStatusProcessor;

    /**
     * @var MergedMessageProcessor
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
            ->getMockBuilder(MessageStatusProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->mergedMessageProcessor = $objectManagerHelper->getObject(
            MergedMessageProcessor::class,
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
        $queue = $this->getMockBuilder(QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration = $this->getMockBuilder(ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration->expects($this->atLeastOnce())->method('getHandlers')->willReturn([]);
        $this->messageStatusProcessor->expects($this->exactly(2))->method('acknowledgeMessages');
        $originalMessage = $this->getMockBuilder(EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mergedMessage = $this->getMockBuilder(MergedMessageInterface::class)
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
        $queue = $this->getMockBuilder(QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration = $this->getMockBuilder(ConsumerConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageStatusProcessor->expects($this->once())->method('acknowledgeMessages');
        $exception = new \Exception();
        $configuration->expects($this->atLeastOnce())->method('getHandlers')->willThrowException($exception);
        $this->messageStatusProcessor->expects($this->atLeastOnce())->method('rejectMessages');
        $originalMessage = $this->getMockBuilder(EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mergedMessage = $this->getMockBuilder(MergedMessageInterface::class)
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

<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\MessageStatusProcessor;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for MessageStatusProcessor.
 */
class MessageStatusProcessorTest extends TestCase
{
    /**
     * @var MessageStatusProcessor
     */
    private $messageStatusProcessor;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->messageStatusProcessor = $objectManagerHelper->getObject(
            MessageStatusProcessor::class
        );
    }

    /**
     * Test for acknowledgeMessages().
     *
     * @return void
     */
    public function testAcknowledgeMessages()
    {
        $queue = $this->createMock(QueueInterface::class);
        $queue->expects($this->atLeastOnce())->method('acknowledge');
        $message = $this->createMock(EnvelopeInterface::class);

        $this->messageStatusProcessor->acknowledgeMessages($queue, [$message]);
    }

    /**
     * Test for rejectMessages().
     *
     * @return void
     */
    public function testRejectMessages()
    {
        $queue = $this->createMock(QueueInterface::class);
        $queue->expects($this->atLeastOnce())->method('reject');
        $message = $this->createMock(EnvelopeInterface::class);

        $this->messageStatusProcessor->rejectMessages($queue, [$message]);
    }
}

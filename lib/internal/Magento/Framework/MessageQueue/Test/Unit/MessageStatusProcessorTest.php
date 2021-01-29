<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for MessageStatusProcessor.
 */
class MessageStatusProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\MessageStatusProcessor
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
            \Magento\Framework\MessageQueue\MessageStatusProcessor::class
        );
    }

    /**
     * Test for acknowledgeMessages().
     *
     * @return void
     */
    public function testAcknowledgeMessages()
    {
        $queue = $this->getMockBuilder(\Magento\Framework\MessageQueue\QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $queue->expects($this->atLeastOnce())->method('acknowledge');
        $message = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->messageStatusProcessor->acknowledgeMessages($queue, [$message]);
    }

    /**
     * Test for rejectMessages().
     *
     * @return void
     */
    public function testRejectMessages()
    {
        $queue = $this->getMockBuilder(\Magento\Framework\MessageQueue\QueueInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $queue->expects($this->atLeastOnce())->method('reject');
        $message = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->messageStatusProcessor->rejectMessages($queue, [$message]);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MysqlMq\Test\Unit\Model\Driver\Bulk;

/**
 * Unit test for bulk Exchange model.
 */
class ExchangeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageQueueConfig;

    /**
     * @var \Magento\MysqlMq\Model\QueueManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueManagement;

    /**
     * @var \Magento\MysqlMq\Model\Driver\Bulk\Exchange
     */
    private $exchange;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->messageQueueConfig = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConfigInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->queueManagement = $this->getMockBuilder(\Magento\MysqlMq\Model\QueueManagement::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->exchange = $objectManager->getObject(
            \Magento\MysqlMq\Model\Driver\Bulk\Exchange::class,
            [
                'messageQueueConfig' => $this->messageQueueConfig,
                'queueManagement' => $this->queueManagement,
            ]
        );
    }

    /**
     * Test for enqueue model.
     *
     * @return void
     */
    public function testEnqueue()
    {
        $topicName = 'topic.name';
        $queueNames = ['queue0', 'queue1'];
        $envelopeBody = 'serializedMessage';
        $this->messageQueueConfig->expects($this->once())
            ->method('getQueuesByTopic')->with($topicName)->willReturn($queueNames);
        $envelope = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableOriginalConstructor()->getMock();
        $envelope->expects($this->once())->method('getBody')->willReturn($envelopeBody);
        $this->queueManagement->expects($this->once())
            ->method('addMessagesToQueues')->with($topicName, [$envelopeBody], $queueNames);
        $this->assertNull($this->exchange->enqueue($topicName, [$envelope]));
    }
}

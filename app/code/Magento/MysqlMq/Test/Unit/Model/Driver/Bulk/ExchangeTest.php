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
     * @var \Magento\Framework\MessageQueue\ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageQueueConfig;

    /**
     * @var \Magento\MysqlMq\Model\QueueManagement|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queueManagement;

    /**
     * @var \Magento\MysqlMq\Model\Driver\Bulk\Exchange
     */
    private $exchange;
    /**
     * @var \Magento\MysqlMq\Model\ConnectionTypeResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connnectionTypeResolver;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->messageQueueConfig = $this->getMockBuilder(
            \Magento\Framework\MessageQueue\Topology\ConfigInterface::class
        )
            ->disableOriginalConstructor()->getMock();
        $this->queueManagement = $this->getMockBuilder(\Magento\MysqlMq\Model\QueueManagement::class)
            ->disableOriginalConstructor()->getMock();
        $this->connnectionTypeResolver = $this->getMockBuilder(\Magento\MysqlMq\Model\ConnectionTypeResolver::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->exchange = $objectManager->getObject(
            \Magento\MysqlMq\Model\Driver\Bulk\Exchange::class,
            [
                'connectionTypeResolver' => $this->connnectionTypeResolver,
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
        $queueNames = ['queue0'];
        $binding1 = $this->createMock(
            \Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface::class
        );
        $binding1->expects($this->once())
            ->method('getTopic')
            ->willReturn($topicName);
        $binding1->expects($this->once())
            ->method('getDestination')
            ->willReturn($queueNames[0]);
        $binding2 = $this->createMock(
            \Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface::class
        );
        $binding2->expects($this->once())
            ->method('getTopic')
            ->willReturn('different.topic');
        $binding2->expects($this->never())
            ->method('getDestination');
        $exchange1 = $this->createMock(
            \Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItemInterface::class
        );
        $exchange1->expects($this->once())
            ->method('getConnection')
            ->willReturn('db');
        $exchange1->expects($this->once())
            ->method('getBindings')
            ->willReturn([$binding1, $binding2]);
        $exchange2 = $this->createMock(
            \Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItemInterface::class
        );
        $exchange2->expects($this->once())
            ->method('getConnection')
            ->willReturn('amqp');
        $exchange2->expects($this->never())
            ->method('getBindings');

        $this->connnectionTypeResolver->method('getConnectionType')->willReturnOnConsecutiveCalls(['db', null]);
        $envelopeBody = 'serializedMessage';
        $this->messageQueueConfig->expects($this->once())
            ->method('getExchanges')->willReturn([$exchange1, $exchange2]);
        $envelope = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableOriginalConstructor()->getMock();
        $envelope->expects($this->once())->method('getBody')->willReturn($envelopeBody);
        $this->queueManagement->expects($this->once())
            ->method('addMessagesToQueues')->with($topicName, [$envelopeBody], $queueNames);
        $this->assertNull($this->exchange->enqueue($topicName, [$envelope]));
    }
}

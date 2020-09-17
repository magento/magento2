<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Test\Unit\Model\Driver\Bulk;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItemInterface;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as TopologyConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MysqlMq\Model\ConnectionTypeResolver;
use Magento\MysqlMq\Model\Driver\Bulk\Exchange;
use Magento\MysqlMq\Model\QueueManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for bulk Exchange model.
 */
class ExchangeTest extends TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\ConfigInterface|MockObject
     */
    private $messageQueueConfig;

    /**
     * @var QueueManagement|MockObject
     */
    private $queueManagement;

    /**
     * @var Exchange
     */
    private $exchange;
    /**
     * @var ConnectionTypeResolver|MockObject
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
            TopologyConfigInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->queueManagement = $this->getMockBuilder(QueueManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connnectionTypeResolver = $this->getMockBuilder(ConnectionTypeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->exchange = $objectManager->getObject(
            Exchange::class,
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
            BindingInterface::class
        );
        $binding1->expects($this->once())
            ->method('getTopic')
            ->willReturn($topicName);
        $binding1->expects($this->once())
            ->method('getDestination')
            ->willReturn($queueNames[0]);
        $binding2 = $this->createMock(
            BindingInterface::class
        );
        $binding2->expects($this->once())
            ->method('getTopic')
            ->willReturn('different.topic');
        $binding2->expects($this->never())
            ->method('getDestination');
        $exchange1 = $this->createMock(
            ExchangeConfigItemInterface::class
        );
        $exchange1->expects($this->once())
            ->method('getConnection')
            ->willReturn('db');
        $exchange1->expects($this->once())
            ->method('getBindings')
            ->willReturn([$binding1, $binding2]);
        $exchange2 = $this->createMock(
            ExchangeConfigItemInterface::class
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
        $envelope = $this->getMockBuilder(EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $envelope->expects($this->once())->method('getBody')->willReturn($envelopeBody);
        $this->queueManagement->expects($this->once())
            ->method('addMessagesToQueues')->with($topicName, [$envelopeBody], $queueNames);
        $this->assertNull($this->exchange->enqueue($topicName, [$envelope]));
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Test\Unit\Model;

use Magento\Framework\MessageQueue\CountableQueueInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\MessageQueue\Model\CheckIsAvailableMessagesInQueue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for CheckIsAvailableMessagesInQueue
 */
class CheckIsAvailableMessagesInQueueTest extends TestCase
{
    /**
     * @var QueueRepository|MockObject
     */
    private $queueRepository;

    /**
     * @var CheckIsAvailableMessagesInQueue
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->queueRepository = $this->createMock(QueueRepository::class);
        $this->model = new CheckIsAvailableMessagesInQueue(
            $this->queueRepository
        );
    }

    public function testExecuteNotCountableAndNotEmptyQueue(): void
    {
        $connectionName = 'test';
        $queueName = 'test';

        $queue = $this->getMockForAbstractClass(QueueInterface::class);
        $message = $this->getMockForAbstractClass(EnvelopeInterface::class);
        $this->queueRepository->expects($this->once())
            ->method('get')
            ->with($connectionName, $queueName)
            ->willReturn($queue);
        $queue->expects($this->once())
            ->method('dequeue')
            ->willReturn($message);
        $queue->expects($this->once())
            ->method('reject')
            ->willReturn($message);
        $this->assertTrue($this->model->execute($connectionName, $queueName));
    }

    public function testExecuteNotCountableAndEmptyQueue(): void
    {
        $connectionName = 'test';
        $queueName = 'test';

        $queue = $this->getMockForAbstractClass(QueueInterface::class);
        $this->queueRepository->expects($this->once())
            ->method('get')
            ->with($connectionName, $queueName)
            ->willReturn($queue);
        $queue->expects($this->once())
            ->method('dequeue')
            ->willReturn(null);
        $this->assertFalse($this->model->execute($connectionName, $queueName));
    }

    public function testExecuteCountableAndNotEmptyQueue(): void
    {
        $connectionName = 'test';
        $queueName = 'test';

        $queue = $this->getMockForAbstractClass(CountableQueueInterface::class);
        $this->queueRepository->expects($this->once())
            ->method('get')
            ->with($connectionName, $queueName)
            ->willReturn($queue);
        $queue->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $queue->expects($this->never())
            ->method('dequeue');
        $this->assertTrue($this->model->execute($connectionName, $queueName));
    }

    public function testExecuteCountableAndEmptyQueue(): void
    {
        $connectionName = 'test';
        $queueName = 'test';

        $queue = $this->getMockForAbstractClass(CountableQueueInterface::class);
        $this->queueRepository->expects($this->once())
            ->method('get')
            ->with($connectionName, $queueName)
            ->willReturn($queue);
        $queue->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $queue->expects($this->never())
            ->method('dequeue');
        $this->assertFalse($this->model->execute($connectionName, $queueName));
    }
}

<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Amqp\Test\Unit\Model\Topology;

use Magento\Amqp\Model\Topology\Synchronizer;
use Magento\Framework\Amqp\TopologyInstaller;
use PhpAmqpLib\Exception\AMQPLogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SynchronizerTest extends TestCase
{
    /**
     * @var TopologyInstaller|MockObject
     */
    private $topologyInstaller;

    /**
     * @var Synchronizer
     */
    private $model;

    protected function setUp(): void
    {
        $this->topologyInstaller = $this->createMock(TopologyInstaller::class);
        $this->model = new Synchronizer($this->topologyInstaller);
    }

    public function testSynchronizeReturnsDeclaredTopology()
    {
        $changes = ['Queue "queue.one" is in place on connection "amqp".'];
        $this->topologyInstaller->expects($this->once())->method('declareTopology')->willReturn($changes);

        $this->assertSame($changes, $this->model->synchronize());
    }

    public function testSynchronizePropagatesBrokerFailure()
    {
        $this->topologyInstaller->expects($this->once())
            ->method('declareTopology')
            ->willThrowException(new AMQPLogicException('Broker is unreachable'));

        $this->expectException(AMQPLogicException::class);
        $this->expectExceptionMessage('Broker is unreachable');

        $this->model->synchronize();
    }
}

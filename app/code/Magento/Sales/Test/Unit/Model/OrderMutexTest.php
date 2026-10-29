<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\DeadlockRecoveryExecutor;
use Magento\Sales\Model\OrderMutex;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderMutexTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapterInterface;

    /**
     * @var DeadlockRecoveryExecutor|MockObject
     */
    private $deadlockRecovery;

    /**
     * @var OrderMutex
     */
    private $orderMutex;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->adapterInterface = $this->createMock(AdapterInterface::class);
        $this->deadlockRecovery = $this->createMock(DeadlockRecoveryExecutor::class);

        $this->orderMutex = new OrderMutex(
            $this->resourceConnection,
            $this->deadlockRecovery
        );
    }

    public function testExecute()
    {
        $orderId = 1;
        $this->resourceConnection->expects($this->once())->method('getConnection')->with('sales')
            ->willReturn($this->adapterInterface);
        $this->deadlockRecovery->expects($this->once())->method('execute')->willReturn('success');
        $result = $this->orderMutex->execute($orderId, fn () => '1');

        $this->assertEquals('success', $result);
    }
}

<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\DeadlockRecoveryExecutor;
use PHPUnit\Framework\TestCase;

class DeadlockRecoveryExecutorTest extends TestCase
{
    private const ATTEMPTS = 5;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapterInterface;

    /**
     * @var DeadlockRecoveryExecutor
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapterInterface = $this->createMock(AdapterInterface::class);

        $this->subject = new DeadlockRecoveryExecutor(self::ATTEMPTS, 0);
    }

    /**
     * Test database operation executes at the first attempt.
     */
    public function testExecuteDeadlock()
    {
        $data = 'success';
        $this->adapterInterface->expects($this->once())
            ->method('beginTransaction')
            ->willReturnSelf();
        $this->adapterInterface->expects($this->once())
            ->method('commit')
            ->willReturnSelf();

        $result = $this->subject->execute($this->adapterInterface, fn () => $data, []);
        $this->assertEquals($data, $result);
    }

    /**
     * Test database operation encounters deadlock, and recovers.
     */
    public function testExecuteDeadlockRecovered()
    {
        $data = 'success';
        $attempts = 0;
        $recoveryAt = 2;
        $callback = function () use (&$attempts, $data, $recoveryAt) {
            $attempts++;
            if ($attempts >= $recoveryAt) {
                return $data;
            }
            throw new DeadlockException();
        };

        $this->adapterInterface->expects($this->exactly($recoveryAt))
            ->method('beginTransaction')
            ->willReturnSelf();
        $this->adapterInterface->expects($this->exactly($recoveryAt-1))
            ->method('rollback')
            ->willReturnSelf();
        $this->adapterInterface->expects($this->once())
            ->method('commit');

        $result = $this->subject->execute($this->adapterInterface, $callback, []);
        $this->assertEquals($data, $result);
    }

    /**
     * Test database operation encounters unrecovered deadlock.
     */
    public function testExecuteDeadlockExhausted()
    {
        $this->expectException(DeadlockException::class);
        $this->adapterInterface->expects($this->exactly(self::ATTEMPTS))
            ->method('beginTransaction')
            ->willReturnSelf();
        $this->adapterInterface->expects($this->exactly(self::ATTEMPTS))
            ->method('rollback')
            ->willReturnSelf();
        $this->adapterInterface->expects($this->never())
            ->method('commit');

        $this->subject->execute($this->adapterInterface, fn () => throw new DeadlockException(), []);
    }

    /**
     * Test LogicException thrown when no attempts performed.
     */
    public function testExecuteDeadlockNotAttempted()
    {
        $this->expectException(\LogicException::class);
        $subject = new DeadlockRecoveryExecutor(0, 0);
        $subject->execute($this->adapterInterface, fn () => 'success', []);
    }
}

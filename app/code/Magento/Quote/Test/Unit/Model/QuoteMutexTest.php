<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use InvalidArgumentException;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\DB\Select;
use Magento\Quote\Model\QuoteMutex;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class QuoteMutexTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private ResourceConnection $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private AdapterInterface $connectionMock;

    /**
     * @var QuoteMutex
     */
    private QuoteMutex $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->resourceConnectionMock->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceConnectionMock->method('getTableName')->with('quote_id_mask')->willReturn('quote_id_mask');
        $this->model = new QuoteMutex($this->resourceConnectionMock);
    }

    public function testExecuteWithEmptyIdsThrowsInvalidArgumentException(): void
    {
        $this->connectionMock->expects($this->never())->method('beginTransaction');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quote masked ids must be provided');

        $this->model->execute([], fn () => true);
    }

    public function testExecuteRunsCallableAndCommits(): void
    {
        $this->mockLockingSelect(['mask1', 'mask2']);
        $this->connectionMock->expects($this->once())->method('beginTransaction');
        $this->connectionMock->expects($this->once())->method('query');
        $this->connectionMock->expects($this->once())->method('commit');
        $this->connectionMock->expects($this->never())->method('rollBack');

        $result = $this->model->execute(['mask1', 'mask2'], fn (string $a, string $b) => $a . $b, ['x', 'y']);

        $this->assertSame('xy', $result);
    }

    public function testExecuteRollsBackWhenCallableThrows(): void
    {
        $exception = new RuntimeException('Callable exception');
        $this->mockLockingSelect(['mask1']);
        $this->connectionMock->expects($this->once())->method('beginTransaction');
        $this->connectionMock->expects($this->once())->method('query');
        $this->connectionMock->expects($this->once())->method('rollBack');
        $this->connectionMock->expects($this->never())->method('commit');

        try {
            $this->model->execute(['mask1'], fn () => throw $exception);
            $this->fail('Expected exception was not thrown');
        } catch (RuntimeException $caught) {
            $this->assertSame($exception, $caught);
        }
    }

    public function testExecuteRollsBackWhenLockQueryFails(): void
    {
        $exception = new LockWaitException('Lock wait timeout exceeded');
        $this->mockLockingSelect(['mask1']);
        $this->connectionMock->expects($this->once())->method('beginTransaction');
        $this->connectionMock->expects($this->once())->method('query')->willThrowException($exception);
        $this->connectionMock->expects($this->once())->method('rollBack');
        $this->connectionMock->expects($this->never())->method('commit');
        $invoked = false;

        try {
            $this->model->execute(['mask1'], function () use (&$invoked) {
                $invoked = true;
            });
            $this->fail('Expected exception was not thrown');
        } catch (LockWaitException $caught) {
            $this->assertSame($exception, $caught);
        }
        $this->assertFalse($invoked);
    }

    private function mockLockingSelect(array $maskedIds): void
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->with('quote_id_mask', 'entity_id')->willReturnSelf();
        $selectMock->method('where')->with('masked_id IN (?)', $maskedIds)->willReturnSelf();
        $selectMock->method('forUpdate')->with(true)->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($selectMock);
    }
}

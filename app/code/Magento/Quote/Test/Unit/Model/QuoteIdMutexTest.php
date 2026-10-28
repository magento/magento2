<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Exception;
use InvalidArgumentException;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\DB\Select;
use Magento\Quote\Model\QuoteIdMutex;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteIdMutexTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    private AdapterInterface $connectionMock;

    /**
     * @var QuoteIdMutex
     */
    private QuoteIdMutex $model;

    protected function setUp(): void
    {
        parent::setUp();
        $selectMock = $this->createStub(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $selectMock->method('forUpdate')->willReturnSelf();
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->connectionMock->method('select')->willReturn($selectMock);
        $resourceConnectionMock = $this->createStub(ResourceConnection::class);
        $resourceConnectionMock->method('getConnection')->willReturn($this->connectionMock);
        $resourceConnectionMock->method('getTableName')->willReturnArgument(0);
        $this->model = new QuoteIdMutex($resourceConnectionMock);
    }

    public function testExecuteWithEmptyIdsThrowsInvalidArgumentException(): void
    {
        $this->connectionMock->expects($this->never())->method('beginTransaction');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quote ids must be provided');

        $this->model->execute([], fn () => true);
    }

    public function testExecuteReturnsCallableResultAndCommits(): void
    {
        $this->connectionMock->expects($this->once())->method('beginTransaction');
        $this->connectionMock->expects($this->once())->method('query');
        $this->connectionMock->expects($this->once())->method('commit');
        $this->connectionMock->expects($this->never())->method('rollBack');

        $result = $this->model->execute([1, 2], fn (string $a, string $b) => $a . $b, ['x', 'y']);

        $this->assertSame('xy', $result);
    }

    public function testExecuteRollsBackWhenCallableThrows(): void
    {
        $exception = new Exception('Callable exception');
        $this->connectionMock->expects($this->once())->method('beginTransaction');
        $this->connectionMock->expects($this->once())->method('rollBack');
        $this->connectionMock->expects($this->never())->method('commit');

        try {
            $this->model->execute([1], fn () => throw $exception);
            $this->fail('Exception was not re-thrown');
        } catch (Exception $caught) {
            $this->assertSame($exception, $caught);
        }
    }

    public function testExecuteRollsBackWhenLockQueryFails(): void
    {
        $exception = new LockWaitException('Lock wait timeout exceeded');
        $callableInvoked = false;
        $this->connectionMock->expects($this->once())->method('beginTransaction');
        $this->connectionMock->expects($this->once())->method('query')->willThrowException($exception);
        $this->connectionMock->expects($this->once())->method('rollBack');
        $this->connectionMock->expects($this->never())->method('commit');

        try {
            $this->model->execute([1], function () use (&$callableInvoked) {
                $callableInvoked = true;
            });
            $this->fail('Exception was not re-thrown');
        } catch (LockWaitException $caught) {
            $this->assertSame($exception, $caught);
        }
        $this->assertFalse($callableInvoked);
    }
}

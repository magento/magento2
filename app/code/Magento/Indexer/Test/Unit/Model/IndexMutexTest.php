<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model;

use Exception;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Indexer\Model\IndexMutex;
use Magento\Framework\Indexer\IndexMutexException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for IndexMutex
 */
class IndexMutexTest extends TestCase
{
    /**
     * @var LockManagerInterface|MockObject
     */
    private $lockManager;

    /**
     * @var ConfigInterface|MockObject
     */
    private $config;

    /**
     * @var IndexMutex
     */
    private $model;

    /**
     * @var false
     */
    private $isCallbackExecuted;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->lockManager = $this->createMock(LockManagerInterface::class);
        $this->config = $this->createMock(ConfigInterface::class);
        $this->model = new IndexMutex(
            $this->lockManager,
            $this->config
        );
        $this->isCallbackExecuted = false;
    }

    public function testLockAcquired(): void
    {
        $indexerName = 'test_indexer';
        $this->config->method('getIndexer')
            ->with($indexerName)
            ->willReturn([]);
        $this->lockManager->expects($this->once())
            ->method('lock')
            ->with('indexer_lock_test_indexer')
            ->willReturn(true);
        $this->lockManager->expects($this->once())
            ->method('unlock')
            ->willReturn(true);
        $this->model->execute($indexerName, [$this, 'callableWithSuccess']);
        $this->assertTrue($this->isCallbackExecuted);
    }

    public function testLockAcquiredForSharedIndex(): void
    {
        $indexerName = 'test_indexer';
        $sharedIndexerName = 'test_shared_indexer';
        $this->config->method('getIndexer')
            ->with($indexerName)
            ->willReturn(['shared_index' => $sharedIndexerName]);
        $this->lockManager->expects($this->once())
            ->method('lock')
            ->with('indexer_lock_test_shared_indexer')
            ->willReturn(true);
        $this->lockManager->expects($this->once())
            ->method('unlock')
            ->willReturn(true);
        $this->model->execute($indexerName, [$this, 'callableWithSuccess']);
        $this->assertTrue($this->isCallbackExecuted);
    }

    public function testLockAcquiredWithCallableWithError(): void
    {
        $this->expectExceptionMessage('test exception');
        $indexerName = 'test_indexer';
        $this->config->method('getIndexer')
            ->with($indexerName)
            ->willReturn([]);
        $this->lockManager->expects($this->once())
            ->method('lock')
            ->with('indexer_lock_test_indexer')
            ->willReturn(true);
        $this->lockManager->expects($this->once())
            ->method('unlock')
            ->willReturn(true);
        $this->model->execute($indexerName, [$this, 'callableWithError']);
        $this->assertTrue($this->isCallbackExecuted);
    }

    public function testLockNotAcquired(): void
    {
        $indexerName = 'test_indexer';
        $exception = new IndexMutexException($indexerName);
        $this->expectExceptionObject($exception);
        $this->lockManager->expects($this->once())
            ->method('lock')
            ->with('indexer_lock_test_indexer')
            ->willReturn(false);
        $this->lockManager->expects($this->never())
            ->method('unlock');
        $this->model->execute($indexerName, [$this, 'callableWithSuccess']);
        $this->assertFalse($this->isCallbackExecuted);
    }

    public function callableWithSuccess(): void
    {
        $this->isCallbackExecuted = true;
    }

    public function callableWithError(): void
    {
        throw new Exception('test exception');
    }
}

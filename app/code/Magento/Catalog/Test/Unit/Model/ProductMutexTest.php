<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Exception;
use Magento\Catalog\Model\ProductMutex;
use Magento\Catalog\Model\ProductMutexException;
use Magento\Framework\Lock\LockManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductMutexTest extends TestCase
{
    private const LOCK_PREFIX = 'product_mutex_';

    private const LOCK_TIMEOUT = 60;

    /**
     * @var ProductMutex
     */
    private $model;

    /**
     * @var LockManagerInterface|MockObject
     */
    private $lockManager;

    protected function setUp(): void
    {
        $this->lockManager = $this->createMock(LockManagerInterface::class);
        $this->model = new ProductMutex($this->lockManager);
    }

    public function testShouldAcquireLockExecuteCallbackReleaseLockAndReturnResult(): void
    {
        $sku = 'sku';
        $callable = function (...$args) {
            return 'result: ' . implode(',', $args);
        };
        $args = ['arg1', 'arg2'];
        $this->lockManager->expects($this->once())
            ->method('lock')
            ->with(self::LOCK_PREFIX . $sku, self::LOCK_TIMEOUT)
            ->willReturn(true);
        $this->lockManager->expects($this->once())
            ->method('unlock')
            ->with(self::LOCK_PREFIX . $sku)
            ->willReturn(true);
        $this->assertEquals('result: arg1,arg2', $this->model->execute($sku, $callable, ...$args));
    }

    public function testShouldThrowExceptionIfLockCannotBeAcquired(): void
    {
        $sku = 'sku';
        $callable = function (...$args) {
            return 'result: ' . implode(',', $args);
        };
        $args = ['arg1', 'arg2'];
        $this->lockManager->expects($this->once())
            ->method('lock')
            ->with(self::LOCK_PREFIX . $sku, self::LOCK_TIMEOUT)
            ->willReturn(false);
        $this->lockManager->expects($this->never())
            ->method('unlock');
        $this->expectException(ProductMutexException::class);
        $this->model->execute($sku, $callable, ...$args);
    }

    public function testShouldReleaseLockIfCallbackThrowsException(): void
    {
        $sku = 'sku';
        $callable = function () {
            throw new Exception('callback exception');
        };
        $args = ['arg1', 'arg2'];
        $this->lockManager->expects($this->once())
            ->method('lock')
            ->with(self::LOCK_PREFIX . $sku, self::LOCK_TIMEOUT)
            ->willReturn(true);
        $this->lockManager->expects($this->once())
            ->method('unlock')
            ->with(self::LOCK_PREFIX . $sku)
            ->willReturn(true);
        $this->expectExceptionMessage('callback exception');
        $this->model->execute($sku, $callable, ...$args);
    }
}

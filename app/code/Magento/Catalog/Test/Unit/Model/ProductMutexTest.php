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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductMutexTest extends TestCase
{
    private const LOCK_PREFIX = 'product_mutex_';

    private const LOCK_TIMEOUT = 60;

    /**
     * MySQL GET_LOCK() rejects longer names
     */
    private const MAX_LOCK_NAME_LENGTH = 64;

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
            ->with($this->lockName($sku), self::LOCK_TIMEOUT)
            ->willReturn(true);
        $this->lockManager->expects($this->once())
            ->method('unlock')
            ->with($this->lockName($sku))
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
            ->with($this->lockName($sku), self::LOCK_TIMEOUT)
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
            ->with($this->lockName($sku), self::LOCK_TIMEOUT)
            ->willReturn(true);
        $this->lockManager->expects($this->once())
            ->method('unlock')
            ->with($this->lockName($sku))
            ->willReturn(true);
        $this->expectExceptionMessage('callback exception');
        $this->model->execute($sku, $callable, ...$args);
    }

    /**
     * SKU spellings that ProductRepository resolves to one catalog_product_entity row must share one lock
     *
     * @param string $first
     * @param string $second
     * @return void
     */
    #[DataProvider('sameProductSkuDataProvider')]
    public function testShouldTakeOneLockForSkuSpellingsOfOneProduct(string $first, string $second): void
    {
        [$firstLock, $secondLock] = $this->acquiredLockNames($first, $second);

        $this->assertSame($firstLock, $secondLock);
    }

    /**
     * @return array
     */
    public static function sameProductSkuDataProvider(): array
    {
        return [
            'different case' => ['erp-1001', 'ERP-1001'],
            'mixed case' => ['Erp-1001', 'eRP-1001'],
            'surrounding whitespace' => ['erp-1001', " erp-1001\t"],
            'case and whitespace' => ['erp-1001', ' ERP-1001 '],
            'multibyte case' => ['ärmel-ü', 'ÄRMEL-Ü'],
        ];
    }

    public function testShouldTakeDifferentLocksForDifferentSkus(): void
    {
        [$firstLock, $secondLock] = $this->acquiredLockNames('erp-1001', 'erp-1002');

        $this->assertNotSame($firstLock, $secondLock);
    }

    public function testShouldKeepTheLockNameWithinTheMysqlLimitForTheLongestSku(): void
    {
        [$lock] = $this->acquiredLockNames(str_repeat('ü', 64));

        $this->assertLessThanOrEqual(self::MAX_LOCK_NAME_LENGTH, strlen($lock));
    }

    /**
     * Run the mutex once per SKU and return the lock names it acquired, in order
     *
     * @param string ...$skus
     * @return string[]
     */
    private function acquiredLockNames(string ...$skus): array
    {
        $acquired = [];
        $this->lockManager->expects($this->exactly(count($skus)))
            ->method('lock')
            ->willReturnCallback(function (string $name) use (&$acquired) {
                $acquired[] = $name;
                return true;
            });
        foreach ($skus as $sku) {
            $this->model->execute($sku, fn () => null);
        }

        return $acquired;
    }

    /**
     * Expected lock name for a SKU
     *
     * @param string $sku
     * @return string
     */
    private function lockName(string $sku): string
    {
        return self::LOCK_PREFIX . hash('xxh128', mb_strtolower(trim($sku)));
    }
}

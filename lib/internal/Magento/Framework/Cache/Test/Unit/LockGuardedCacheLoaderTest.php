<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit;

use Magento\Framework\Cache\LockGuardedCacheLoader;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LockGuardedCacheLoaderTest extends TestCase
{
    /**
     * @var LockManagerInterface|MockObject
     */
    private $lockManagerInterfaceMock;

    /**
     * @var LockGuardedCacheLoader
     */
    private $LockGuardedCacheLoader;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->lockManagerInterfaceMock = $this->getMockForAbstractClass(LockManagerInterface::class);

        $objectManager = new ObjectManagerHelper($this);

        $this->LockGuardedCacheLoader = $objectManager->getObject(
            LockGuardedCacheLoader::class,
            [
                'locker' => $this->lockManagerInterfaceMock
            ]
        );
    }

    /**
     * Verify optimistic data read from cache.
     *
     * @return void
     */
    public function testOptimisticDataRead(): void
    {
        $lockName = \uniqid('lock_name_1_', true);

        $dataLoader = function () {
            return 'loaded_data';
        };

        $dataCollector = function () {
            return true;
        };

        $dataSaver = function () {
            return true;
        };

        $this->lockManagerInterfaceMock->expects($this->never())->method('lock');
        $this->lockManagerInterfaceMock->expects($this->never())->method('unlock');

        $this->assertEquals(
            'loaded_data',
            $this->LockGuardedCacheLoader->lockedLoadData($lockName, $dataLoader, $dataCollector, $dataSaver)
        );
    }

    /**
     * Verify data is collected when deadline to read from cache is reached.
     *
     * @return void
     */
    public function testDataCollectedAfterDeadlineReached(): void
    {
        $lockName = \uniqid('lock_name_1_', true);

        $dataLoader = function () {
            return false;
        };

        $dataCollector = function () {
            return 'collected_data';
        };

        $dataSaver = function () {
            return true;
        };

        $this->lockManagerInterfaceMock
            ->expects($this->atLeastOnce())->method('lock')
            ->with($lockName, 0)
            ->willReturn(false);

        $this->lockManagerInterfaceMock->expects($this->never())->method('unlock');

        $this->assertEquals(
            'collected_data',
            $this->LockGuardedCacheLoader->lockedLoadData($lockName, $dataLoader, $dataCollector, $dataSaver)
        );
    }

    /**
     * Verify data write to cache.
     *
     * @return void
     */
    public function testDataWrite(): void
    {
        $lockName = \uniqid('lock_name_1_', true);

        $dataLoader = function () {
            return false;
        };

        $dataCollector = function () {
            return 'collected_data';
        };

        $dataSaver = function () {
            return true;
        };

        $this->lockManagerInterfaceMock
            ->expects($this->once())->method('lock')
            ->with($lockName, 0)
            ->willReturn(true);

        $this->lockManagerInterfaceMock->expects($this->once())->method('unlock');

        $this->assertEquals(
            'collected_data',
            $this->LockGuardedCacheLoader->lockedLoadData($lockName, $dataLoader, $dataCollector, $dataSaver)
        );
    }

    /**
     * Verify data collected when Parallel Generation is allowed.
     *
     * @return void
     */
    public function testDataCollectedWithParallelGeneration(): void
    {
        $lockName = \uniqid('lock_name_1_', true);

        $dataLoader = function () {
            return false;
        };

        $dataCollector = function () {
            return 'collected_data';
        };

        $dataSaver = function () {
            return true;
        };

        $closure = \Closure::bind(function ($cacheLoader) {
            return $cacheLoader->allowParallelGenerationConfigValue = true;
        }, null, $this->LockGuardedCacheLoader);
        $closure($this->LockGuardedCacheLoader);

        $this->lockManagerInterfaceMock
            ->expects($this->once())->method('lock')
            ->with($lockName, 0)
            ->willReturn(false);

        $this->lockManagerInterfaceMock->expects($this->never())->method('unlock');

        $this->assertEquals(
            'collected_data',
            $this->LockGuardedCacheLoader->lockedLoadData($lockName, $dataLoader, $dataCollector, $dataSaver)
        );
    }
}

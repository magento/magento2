<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Sales\Model\GridAsyncInsert;
use Magento\Sales\Model\ResourceModel\GridInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GridAsyncInsertTest extends TestCase
{

    /**
     * @var GridInterface|MockObject
     */
    private $grid;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var LockManagerInterface|MockObject
     */
    private $lockManager;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    protected function setUp(): void
    {
        $this->grid = $this->createMock(GridInterface::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->lockManager = $this->createMock(LockManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->scopeConfig
            ->method('getValue')
            ->with('dev/grid/async_indexing')
            ->willReturn('1');
    }

    public function testAsyncInsertSkipsWhenLocked(): void
    {
        $this->grid->expects($this->once())->method('refreshBySchedule');

        $model = new GridAsyncInsert(
            $this->grid,
            $this->scopeConfig
        );
        $model->asyncInsert();
    }

    public function testAsyncInsertExecutesWhenLockAcquired(): void
    {
        $this->grid->expects($this->once())->method('refreshBySchedule');

        $model = new GridAsyncInsert(
            $this->grid,
            $this->scopeConfig
        );
        $model->asyncInsert();
    }
}

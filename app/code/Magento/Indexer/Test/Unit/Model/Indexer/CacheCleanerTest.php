<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Indexer;

use Magento\Framework\Indexer\ActionInterface;
use Magento\Indexer\Model\Indexer\CacheCleaner;
use Magento\Indexer\Model\Indexer\DeferredCacheCleaner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test cache cleaner plugin
 */
class CacheCleanerTest extends TestCase
{
    /**
     * @var CacheCleaner
     */
    private $model;

    /**
     * @var ActionInterface|MockObject
     */
    private $action;

    /**
     * @var DeferredCacheCleaner|MockObject
     */
    private $cacheCleaner;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->action = $this->getMockForAbstractClass(ActionInterface::class);
        $this->cacheCleaner = $this->createMock(DeferredCacheCleaner::class);
        $this->model = new CacheCleaner($this->cacheCleaner);
    }

    /**
     * Test beforeExecuteFull()
     */
    public function testBeforeExecuteFull(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('start');
        $this->model->beforeExecuteFull($this->action);
    }

    /**
     * Test afterExecuteFull()
     */
    public function testAfterExecuteFull(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('flush');
        $this->model->afterExecuteFull($this->action);
    }

    /**
     * Test beforeExecuteList()
     */
    public function testBeforeExecuteList(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('start');
        $this->model->beforeExecuteList($this->action);
    }

    /**
     * Test afterExecuteList()
     */
    public function testAfterExecuteList(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('flush');
        $this->model->afterExecuteList($this->action);
    }

    /**
     * Test beforeExecuteRow()
     */
    public function testBeforeExecuteRow(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('start');
        $this->model->beforeExecuteRow($this->action);
    }

    /**
     * Test afterExecuteRow()
     */
    public function testAfterExecuteRow(): void
    {
        $this->cacheCleaner->expects($this->once())
            ->method('flush');
        $this->model->afterExecuteRow($this->action);
    }
}

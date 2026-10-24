<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Config\Backend;

use Magento\CatalogInventory\Api\StockIndexInterface;
use Magento\CatalogInventory\Model\Config\Backend\Managestock;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ManagestockTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $configMock;

    /** @var  Processor|MockObject */
    protected $stockIndexerProcessor;

    /** @var Managestock */
    protected $model;

    protected function setUp(): void
    {
        $this->stockIndexerProcessor = $this->createMock(Processor::class);
        $this->configMock = $this->createMock(ScopeConfigInterface::class);

        $contextMock = $this->createMock(Context::class);
        $registryMock = $this->createMock(Registry::class);
        $cacheTypeListMock = $this->createMock(TypeListInterface::class);
        $stockIndexMock = $this->createMock(StockIndexInterface::class);

        $eventDispatcherMock = $this->createMock(EventManagerInterface::class);
        $contextMock->method('getEventDispatcher')->willReturn($eventDispatcherMock);

        $this->model = new Managestock(
            $contextMock,
            $registryMock,
            $this->configMock,
            $cacheTypeListMock,
            $stockIndexMock,
            $this->stockIndexerProcessor
        );
    }

    /**
     * Data provider for testSaveAndRebuildIndex
     * @return array
     */
    public static function saveAndRebuildIndexDataProvider()
    {
        return [
            [1, 1],
            [0, 0],
        ];
    }

    /**
     *
     * @param int $newStockValue new value for stock status
     * @param int $callCount count matcher
     */
    #[DataProvider('saveAndRebuildIndexDataProvider')]
    public function testSaveAndRebuildIndex($newStockValue, $callCount)
    {
        $this->model->setValue($newStockValue);
        $this->stockIndexerProcessor->expects($this->exactly($callCount))->method('markIndexerAsInvalid');
        $this->configMock->method('getValue')->willReturn(0);   // old value for stock status

        $this->model->afterSave();
    }
}

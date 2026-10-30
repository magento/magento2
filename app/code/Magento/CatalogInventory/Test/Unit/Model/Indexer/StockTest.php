<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer;

use Magento\CatalogInventory\Model\Indexer\Stock\Action\Row;
use Magento\CatalogInventory\Model\Indexer\Stock\Action\Rows;
use Magento\CatalogInventory\Model\Indexer\Stock\Action\Full;
use Magento\CatalogInventory\Model\Indexer\Stock;
use Magento\Framework\Indexer\CacheContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StockTest extends TestCase
{
    /**
     * @var MockObject|Row
     */
    private $productStockIndexerRow;

    /**
     * @var MockObject|Rows
     */
    private $productStockIndexerRows;

    /**
     * @var MockObject|Full
     */
    private $productStockIndexerFull;

    /**
     * @var MockObject|CacheContext
     */
    private $cacheContext;

    /**
     * @var Stock
     */
    private $stock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->productStockIndexerRow = $this->createMock(Row::class);
        $this->productStockIndexerRows = $this->createMock(Rows::class);
        $this->productStockIndexerFull = $this->createMock(Full::class);
        $this->cacheContext = $this->createMock(CacheContext::class);

        $this->stock = new Stock(
            $this->productStockIndexerRow,
            $this->productStockIndexerRows,
            $this->productStockIndexerFull,
            $this->cacheContext
        );
    }

    /**
     * Validate that cache context not used during execution
     *
     * @return void
     */
    public function testExecute(): void
    {
        $ids = [1, 2, 3];
        $this->productStockIndexerRows->expects($this->once())
            ->method('execute')
            ->with($ids);
        $this->cacheContext->expects($this->never())
            ->method('registerEntities');
        $this->stock->execute($ids);
    }
}

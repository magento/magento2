<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\ResourceModel\Indexer;

use Magento\Bundle\Model\ResourceModel\Indexer\DefaultInventoryStockStatusQueryProcessor;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultInventoryStockStatusQueryProcessorTest extends TestCase
{
    /** @var ResourceConnection|MockObject */
    private ResourceConnection $resource;

    /** @var Select|MockObject */
    private Select $select;

    /**
     * @var DefaultInventoryStockStatusQueryProcessor
     */
    private DefaultInventoryStockStatusQueryProcessor $processor;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->select = $this->createMock(Select::class);

        $this->processor = new DefaultInventoryStockStatusQueryProcessor($this->resource);
    }

    /**
     * @return void
     */
    public function testExecuteJoinsCataloginventoryStockStatus(): void
    {
        $this->resource->expects($this->once())
            ->method('getTableName')
            ->with('cataloginventory_stock_status')
            ->willReturn('cataloginventory_stock_status');

        $this->select->expects($this->once())
            ->method('join')
            ->with(
                ['si' => 'cataloginventory_stock_status'],
                'si.product_id = bs.product_id',
                []
            )
            ->willReturnSelf();

        $this->select->expects($this->once())
            ->method('where')
            ->with('stock_status = ?', Stock::STOCK_IN_STOCK)
            ->willReturnSelf();

        $result = $this->processor->execute($this->select);

        $this->assertSame($this->select, $result);
    }
}

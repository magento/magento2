<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Source;

use Magento\CatalogInventory\Model\Source\Stock;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\TestCase;

class StockTest extends TestCase
{
    /**
     * @var Stock
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new Stock();
    }

    public function testAddValueSortToCollection()
    {
        $selectMock = $this->createMock(Select::class);
        $collectionMock = $this->createMock(AbstractCollection::class);
        $collectionMock->expects($this->atLeastOnce())->method('getSelect')->willReturn($selectMock);
        $collectionMock->expects($this->atLeastOnce())->method('getTable')->willReturn('cataloginventory_stock_item');

        $selectMock->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['stock_item_table' => 'cataloginventory_stock_item'],
                "e.entity_id=stock_item_table.product_id",
                []
            )
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('order')
            ->with("stock_item_table.qty DESC")
            ->willReturnSelf();

        $this->model->addValueSortToCollection($collectionMock);
    }
}

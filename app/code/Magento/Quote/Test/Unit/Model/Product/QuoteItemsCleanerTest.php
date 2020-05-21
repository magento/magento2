<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Quote\Model\Product\QuoteItemsCleaner;
use Magento\Quote\Model\ResourceModel\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteItemsCleanerTest extends TestCase
{
    /**
     * @var QuoteItemsCleaner
     */
    private $model;

    /**
     * @var MockObject|Item
     */
    private $itemResourceMock;

    protected function setUp(): void
    {
        $this->itemResourceMock = $this->createMock(Item::class);
        $this->model = new QuoteItemsCleaner($this->itemResourceMock);
    }

    public function testExecute()
    {
        $tableName = 'table_name';
        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock->expects($this->once())->method('getId')->willReturn(1);

        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->itemResourceMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $this->itemResourceMock->expects($this->once())->method('getMainTable')->willReturn($tableName);

        $connectionMock->expects($this->once())->method('delete')->with($tableName, 'product_id = 1');
        $this->model->execute($productMock);
    }
}

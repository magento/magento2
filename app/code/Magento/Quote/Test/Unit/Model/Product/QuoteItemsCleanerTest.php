<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Product;

class QuoteItemsCleanerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\Product\QuoteItemsCleaner
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Quote\Model\ResourceModel\Quote\Item
     */
    private $itemResourceMock;

    protected function setUp(): void
    {
        $this->itemResourceMock = $this->createMock(\Magento\Quote\Model\ResourceModel\Quote\Item::class);
        $this->model = new \Magento\Quote\Model\Product\QuoteItemsCleaner($this->itemResourceMock);
    }

    public function testExecute()
    {
        $tableName = 'table_name';
        $productMock = $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class);
        $productMock->expects($this->once())->method('getId')->willReturn(1);

        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->itemResourceMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $this->itemResourceMock->expects($this->once())->method('getMainTable')->willReturn($tableName);

        $connectionMock->expects($this->once())->method('delete')->with($tableName, 'product_id = 1');
        $this->model->execute($productMock);
    }
}

<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Action;

class EraserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $connection;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $indexerHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser
     */
    protected $model;

    protected function setUp(): void
    {
        $resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $resource->expects($this->any())->method('getConnection')->willReturn($this->connection);
        $this->indexerHelper = $this->createMock(\Magento\Catalog\Helper\Product\Flat\Indexer::class);
        $this->indexerHelper->expects($this->any())->method('getTable')->willReturnArgument(0);
        $this->indexerHelper->expects($this->any())->method('getFlatTableName')->willReturnMap([
            [1, 'store_1_flat'],
            [2, 'store_2_flat'],
        ]);

        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->model = new \Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser(
            $resource,
            $this->indexerHelper,
            $this->storeManager
        );
    }

    public function testRemoveDeletedProducts()
    {
        $productsToDeleteIds = [1, 2];
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects($this->once())
            ->method('from')
            ->with(['product_table' => 'catalog_product_entity'])
            ->willReturnSelf();
        $select->expects($this->once())->method('columns')->with('entity_id')->willReturnSelf();
        $select->expects($this->once())
            ->method('where')
            ->with('product_table.entity_id IN(?)', $productsToDeleteIds)
            ->willReturnSelf();
        $products = [['entity_id' => 2]];
        $statement = $this->createMock(\Zend_Db_Statement_Interface::class);
        $statement->expects($this->once())->method('fetchAll')->willReturn($products);
        $this->connection->expects($this->once())->method('query')->with($select)
            ->willReturn($statement);
        $this->connection->expects($this->once())->method('select')->willReturn($select);
        $this->connection->expects($this->once())->method('delete')
            ->with('store_1_flat', ['entity_id IN(?)' => [1]]);

        $this->model->removeDeletedProducts($productsToDeleteIds, 1);
    }

    public function testDeleteProductsFromStoreForAllStores()
    {
        $store1 = $this->createMock(\Magento\Store\Model\Store::class);
        $store1->expects($this->any())->method('getId')->willReturn(1);
        $store2 = $this->createMock(\Magento\Store\Model\Store::class);
        $store2->expects($this->any())->method('getId')->willReturn(2);
        $this->storeManager->expects($this->once())->method('getStores')
            ->willReturn([$store1, $store2]);
        $this->connection->expects($this->at(0))->method('delete')
            ->with('store_1_flat', ['entity_id IN(?)' => [1]]);
        $this->connection->expects($this->at(1))->method('delete')
            ->with('store_2_flat', ['entity_id IN(?)' => [1]]);

        $this->model->deleteProductsFromStore(1);
    }
}

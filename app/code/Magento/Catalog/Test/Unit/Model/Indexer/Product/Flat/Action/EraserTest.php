<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Helper\Product\Flat\Indexer;
use Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EraserTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $connection;

    /**
     * @var MockObject
     */
    protected $indexerHelper;

    /**
     * @var MockObject
     */
    protected $storeManager;

    /**
     * @var Eraser
     */
    protected $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $resource = $this->createMock(ResourceConnection::class);
        $this->connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $resource->expects($this->any())->method('getConnection')->willReturn($this->connection);
        $this->indexerHelper = $this->createMock(Indexer::class);
        $this->indexerHelper->expects($this->any())->method('getTable')->willReturnArgument(0);
        $this->indexerHelper->expects($this->any())->method('getFlatTableName')->willReturnMap([
            [1, 'store_1_flat'],
            [2, 'store_2_flat'],
        ]);

        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->model = new Eraser(
            $resource,
            $this->indexerHelper,
            $this->storeManager
        );
    }

    /**
     * @return void
     */
    public function testRemoveDeletedProducts(): void
    {
        $productsToDeleteIds = [1, 2];
        $select = $this->createMock(Select::class);
        $select->expects($this->once())
            ->method('from')
            ->with(['product_table' => 'catalog_product_entity'])->willReturnSelf();
        $select->expects($this->once())->method('columns')->with('entity_id')->willReturnSelf();
        $select->expects($this->once())
            ->method('where')
            ->with('product_table.entity_id IN(?)', $productsToDeleteIds)->willReturnSelf();
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

    /**
     * @return void
     */
    public function testDeleteProductsFromStoreForAllStores(): void
    {
        $store1 = $this->createMock(Store::class);
        $store1->expects($this->any())->method('getId')->willReturn(1);
        $store2 = $this->createMock(Store::class);
        $store2->expects($this->any())->method('getId')->willReturn(2);
        $this->storeManager->expects($this->once())->method('getStores')
            ->willReturn([$store1, $store2]);
        $this->connection
            ->method('delete')
            ->withConsecutive(
                ['store_1_flat', ['entity_id IN(?)' => [1]]],
                ['store_2_flat', ['entity_id IN(?)' => [1]]]
            );

        $this->model->deleteProductsFromStore(1);
    }
}

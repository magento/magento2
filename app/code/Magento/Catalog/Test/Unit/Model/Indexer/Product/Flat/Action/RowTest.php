<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Action;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RowTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productIndexerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $flatItemWriter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $flatItemEraser;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $flatTableBuilder;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->resource->expects($this->any())->method('getConnection')
            ->with('default')
            ->will($this->returnValue($this->connection));
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->store->expects($this->any())->method('getId')->will($this->returnValue('store_id_1'));
        $this->storeManager->expects($this->any())->method('getStores')->will($this->returnValue([$this->store]));
        $this->productIndexerHelper = $this->createMock(\Magento\Catalog\Helper\Product\Flat\Indexer::class);
        $this->flatItemEraser = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser::class);
        $this->flatItemWriter = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Flat\Action\Indexer::class);
        $this->flatTableBuilder = $this->createMock(
            \Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder::class
        );

        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row::class,
            [
                'resource' => $this->resource,
                'storeManager' => $this->storeManager,
                'productHelper' => $this->productIndexerHelper,
                'flatItemEraser' => $this->flatItemEraser,
                'flatItemWriter' => $this->flatItemWriter,
                'flatTableBuilder' => $this->flatTableBuilder
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage We can't rebuild the index for an undefined product.
     */
    public function testExecuteWithEmptyId()
    {
        $this->model->execute(null);
    }

    public function testExecuteWithNonExistingFlatTablesCreatesTables()
    {
        $this->productIndexerHelper->expects($this->any())->method('getFlatTableName')
            ->will($this->returnValue('store_flat_table'));
        $this->connection->expects($this->any())->method('isTableExists')->with('store_flat_table')
            ->will($this->returnValue(false));
        $this->flatItemEraser->expects($this->never())->method('removeDeletedProducts');
        $this->flatTableBuilder->expects($this->once())->method('build')->with('store_id_1', ['product_id_1']);
        $this->flatItemWriter->expects($this->once())->method('write')->with('store_id_1', 'product_id_1');
        $this->model->execute('product_id_1');
    }

    public function testExecuteWithExistingFlatTablesCreatesTables()
    {
        $this->productIndexerHelper->expects($this->any())->method('getFlatTableName')
            ->will($this->returnValue('store_flat_table'));
        $this->connection->expects($this->any())->method('isTableExists')->with('store_flat_table')
            ->will($this->returnValue(true));
        $this->flatItemEraser->expects($this->once())->method('removeDeletedProducts');
        $this->flatTableBuilder->expects($this->never())->method('build')->with('store_id_1', ['product_id_1']);
        $this->model->execute('product_id_1');
    }
}

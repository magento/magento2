<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Action;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RowTest extends \PHPUnit_Framework_TestCase
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

        $this->connection = $this->getMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->resource = $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false);
        $this->resource->expects($this->any())->method('getConnection')
            ->with('default')
            ->will($this->returnValue($this->connection));
        $this->storeManager = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->store = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->store->expects($this->any())->method('getId')->will($this->returnValue('store_id_1'));
        $this->storeManager->expects($this->any())->method('getStores')->will($this->returnValue([$this->store]));
        $this->productIndexerHelper = $this->getMock(
            \Magento\Catalog\Helper\Product\Flat\Indexer::class, [], [], '', false
        );
        $this->flatItemEraser = $this->getMock(
            \Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser::class, [], [], '', false
        );
        $this->flatItemWriter = $this->getMock(
            \Magento\Catalog\Model\Indexer\Product\Flat\Action\Indexer::class, [], [], '', false
        );
        $this->flatTableBuilder = $this->getMock(
            \Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder::class, [], [], '', false
        );

        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row::class, [
            'resource' => $this->resource,
            'storeManager' => $this->storeManager,
            'productHelper' => $this->productIndexerHelper,
            'flatItemEraser' => $this->flatItemEraser,
            'flatItemWriter' => $this->flatItemWriter,
            'flatTableBuilder' => $this->flatTableBuilder
        ]);
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


<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\TestFramework\Helper\ObjectManager;

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

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->connection = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface');
        $this->resource = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->resource->expects($this->any())->method('getConnection')
            ->with('default')
            ->will($this->returnValue($this->connection));
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->store->expects($this->any())->method('getId')->will($this->returnValue('store_id_1'));
        $this->storeManager->expects($this->any())->method('getStores')->will($this->returnValue([$this->store]));
        $this->productIndexerHelper = $this->getMock(
            'Magento\Catalog\Helper\Product\Flat\Indexer', [], [], '', false
        );
        $this->flatItemEraser = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser', [], [], '', false
        );
        $this->flatItemWriter = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Flat\Action\Indexer', [], [], '', false
        );
        $this->flatTableBuilder = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder', [], [], '', false
        );

        $this->model = $objectManager->getObject('Magento\Catalog\Model\Indexer\Product\Flat\Action\Row', [
            'resource' => $this->resource,
            'storeManager' => $this->storeManager,
            'productHelper' => $this->productIndexerHelper,
            'flatItemEraser' => $this->flatItemEraser,
            'flatItemWriter' => $this->flatItemWriter,
            'flatTableBuilder' => $this->flatTableBuilder
        ]);
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Could not rebuild index for undefined product
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


<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Action;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RowsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_store;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productIndexerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_connection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_flatItemWriter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_flatItemEraser;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_flatTableBuilder;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->_connection = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface');
        $this->_resource = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $this->_resource->expects($this->any())->method('getConnection')
            ->with('default')
            ->will($this->returnValue($this->_connection));
        $this->_storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->_store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->_store->expects($this->any())->method('getId')->will($this->returnValue('store_id_1'));
        $this->_storeManager->expects($this->any())->method('getStores')->will(
            $this->returnValue([$this->_store])
        );
        $this->_productIndexerHelper = $this->getMock(
            'Magento\Catalog\Helper\Product\Flat\Indexer', [], [], '', false
        );
        $this->_flatItemEraser = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser', [], [], '', false
        );
        $this->_flatItemWriter = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Flat\Action\Indexer', [], [], '', false
        );
        $this->_flatTableBuilder = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder', [], [], '', false
        );

        $this->_model = $objectManager->getObject('Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows', [
            'resource' => $this->_resource,
            'storeManager' => $this->_storeManager,
            'productHelper' => $this->_productIndexerHelper,
            'flatItemEraser' => $this->_flatItemEraser,
            'flatItemWriter' => $this->_flatItemWriter,
            'flatTableBuilder' => $this->_flatTableBuilder
        ]);
    }
    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Bad value was supplied.
     */
    public function testEmptyIds()
    {
        $this->_model->execute(null);
    }

    public function testExecuteWithNonExistingFlatTablesCreatesTables()
    {
        $this->_productIndexerHelper->expects($this->any())->method('getFlatTableName')
            ->will($this->returnValue('store_flat_table'));
        $this->_connection->expects($this->any())->method('isTableExists')->with('store_flat_table')
            ->will($this->returnValue(false));
        $this->_flatItemEraser->expects($this->never())->method('removeDeletedProducts');
        $this->_flatTableBuilder->expects($this->once())->method('build')->with('store_id_1', [1, 2]);
        $this->_model->execute([1, 2]);
    }

    public function testExecuteWithExistingFlatTablesCreatesTables()
    {
        $this->_productIndexerHelper->expects($this->any())->method('getFlatTableName')
            ->will($this->returnValue('store_flat_table'));
        $this->_connection->expects($this->any())->method('isTableExists')->with('store_flat_table')
            ->will($this->returnValue(true));
        $this->_flatItemEraser->expects($this->once())->method('removeDeletedProducts');
        $this->_flatTableBuilder->expects($this->once())->method('build')->with('store_id_1', [1, 2]);
        $this->_model->execute([1, 2]);
    }
}

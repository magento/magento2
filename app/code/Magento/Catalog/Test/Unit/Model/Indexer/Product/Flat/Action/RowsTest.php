<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Action;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RowsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_store;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_productIndexerHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_resource;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_connection;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_flatItemWriter;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_flatItemEraser;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_flatTableBuilder;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->_connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->_resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->_resource->expects($this->any())->method('getConnection')
            ->with('default')
            ->willReturn($this->_connection);
        $this->_storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->_store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->_store->expects($this->any())->method('getId')->willReturn('store_id_1');
        $this->_storeManager->expects($this->any())->method('getStores')->willReturn(
            [$this->_store]
        );
        $this->_productIndexerHelper = $this->createMock(\Magento\Catalog\Helper\Product\Flat\Indexer::class);
        $this->_flatItemEraser = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser::class);
        $this->_flatItemWriter = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Flat\Action\Indexer::class);
        $this->_flatTableBuilder = $this->createMock(
            \Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder::class
        );

        $this->_model = $objectManager->getObject(
            \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows::class,
            [
                'resource' => $this->_resource,
                'storeManager' => $this->_storeManager,
                'productHelper' => $this->_productIndexerHelper,
                'flatItemEraser' => $this->_flatItemEraser,
                'flatItemWriter' => $this->_flatItemWriter,
                'flatTableBuilder' => $this->_flatTableBuilder
            ]
        );
    }

    /**
     */
    public function testEmptyIds()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Bad value was supplied.');

        $this->_model->execute(null);
    }

    public function testExecuteWithNonExistingFlatTablesCreatesTables()
    {
        $this->_productIndexerHelper->expects($this->any())->method('getFlatTableName')
            ->willReturn('store_flat_table');
        $this->_connection->expects($this->any())->method('isTableExists')->with('store_flat_table')
            ->willReturn(false);
        $this->_flatItemEraser->expects($this->never())->method('removeDeletedProducts');
        $this->_flatTableBuilder->expects($this->once())->method('build')->with('store_id_1', [1, 2]);
        $this->_model->execute([1, 2]);
    }

    public function testExecuteWithExistingFlatTablesCreatesTables()
    {
        $this->_productIndexerHelper->expects($this->any())->method('getFlatTableName')
            ->willReturn('store_flat_table');
        $this->_connection->expects($this->any())->method('isTableExists')->with('store_flat_table')
            ->willReturn(true);
        $this->_flatItemEraser->expects($this->once())->method('removeDeletedProducts');
        $this->_flatTableBuilder->expects($this->once())->method('build')->with('store_id_1', [1, 2]);
        $this->_model->execute([1, 2]);
    }
}

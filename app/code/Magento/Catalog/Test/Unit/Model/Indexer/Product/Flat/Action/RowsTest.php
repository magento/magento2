<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Helper\Product\Flat\Indexer;
use Magento\Catalog\Model\Indexer\Product\Flat\Action\Eraser;
use Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows;
use Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RowsTest extends TestCase
{
    /**
     * @var Rows
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_storeManager;

    /**
     * @var MockObject
     */
    protected $_store;

    /**
     * @var MockObject
     */
    protected $_productIndexerHelper;

    /**
     * @var MockObject
     */
    protected $_resource;

    /**
     * @var MockObject
     */
    protected $_connection;

    /**
     * @var MockObject
     */
    protected $_flatItemWriter;

    /**
     * @var MockObject
     */
    protected $_flatItemEraser;

    /**
     * @var MockObject
     */
    protected $_flatTableBuilder;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->_connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->_resource = $this->createMock(ResourceConnection::class);
        $this->_resource->expects($this->any())->method('getConnection')
            ->with('default')
            ->willReturn($this->_connection);
        $this->_storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->_store = $this->createMock(Store::class);
        $this->_store->expects($this->any())->method('getId')->willReturn('store_id_1');
        $this->_storeManager->expects($this->any())->method('getStores')->willReturn(
            [$this->_store]
        );
        $this->_productIndexerHelper = $this->createMock(Indexer::class);
        $this->_flatItemEraser = $this->createMock(Eraser::class);
        $this->_flatItemWriter = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Flat\Action\Indexer::class);
        $this->_flatTableBuilder = $this->createMock(
            FlatTableBuilder::class
        );

        $this->_model = $objectManager->getObject(
            Rows::class,
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

    public function testEmptyIds()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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

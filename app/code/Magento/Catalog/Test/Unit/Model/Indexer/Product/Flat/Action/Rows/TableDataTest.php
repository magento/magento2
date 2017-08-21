<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Action\Rows;

use Magento\Framework\App\ResourceConnection;

class TableDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_connectionMock;

    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productIndexerHelper;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->_resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->_productIndexerHelper = $this->createMock(\Magento\Catalog\Helper\Product\Flat\Indexer::class);
    }

    public function testMoveWithNonExistentFlatTable()
    {
        $flatTable = 'flat_table';
        $flatDropName = 'flat_table_to_drop';
        $temporaryFlatTableName = 'flat_tmp';

        $this->_connectionMock->expects($this->exactly(2))->method('dropTable')->with($flatDropName);
        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'isTableExists'
        )->with(
            $flatTable
        )->will(
            $this->returnValue(false)
        );

        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'renameTablesBatch'
        )->with(
            [['oldName' => 'flat_tmp', 'newName' => 'flat_table']]
        );

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($this->_connectionMock)
        );

        $model = $this->_objectManager->getObject(
            \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows\TableData::class,
            ['resource' => $this->_resourceMock, 'productIndexerHelper' => $this->_productIndexerHelper]
        );

        $model->move($flatTable, $flatDropName, $temporaryFlatTableName);
    }

    public function testMoveWithExistentFlatTable()
    {
        $flatTable = 'flat_table';
        $flatDropName = 'flat_table_to_drop';
        $temporaryFlatTableName = 'flat_tmp';

        $describedColumns = [
            'column_11' => 'column_definition',
            'column_2' => 'column_definition',
            'column_3' => 'column_definition',
        ];

        $flatColumns = [
            'column_1' => 'column_definition',
            'column_2' => 'column_definition',
            'column_3' => 'column_definition',
        ];

        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects(
            $this->once()
        )->method(
            'from'
        )->with(
            ['tf' => sprintf('%s_tmp_indexer', $flatTable)],
            ['column_2', 'column_3']
        );
        $sql = md5(time());
        $selectMock->expects(
            $this->once()
        )->method(
            'insertFromSelect'
        )->with(
            $flatTable,
            ['column_2', 'column_3']
        )->will(
            $this->returnValue($sql)
        );

        $this->_connectionMock->expects($this->once())->method('query')->with($sql);

        $this->_connectionMock->expects($this->once())->method('select')->will($this->returnValue($selectMock));

        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'isTableExists'
        )->with(
            $flatTable
        )->will(
            $this->returnValue(true)
        );

        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'describeTable'
        )->with(
            $flatTable
        )->will(
            $this->returnValue($describedColumns)
        );

        $this->_productIndexerHelper->expects(
            $this->once()
        )->method(
            'getFlatColumns'
        )->will(
            $this->returnValue($flatColumns)
        );

        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'dropTable'
        )->with(
            sprintf('%s_tmp_indexer', $flatTable)
        );

        $this->_resourceMock->expects(
            $this->any()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($this->_connectionMock)
        );

        $model = $this->_objectManager->getObject(
            \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows\TableData::class,
            ['resource' => $this->_resourceMock, 'productIndexerHelper' => $this->_productIndexerHelper]
        );

        $model->move($flatTable, $flatDropName, $temporaryFlatTableName);
    }
}

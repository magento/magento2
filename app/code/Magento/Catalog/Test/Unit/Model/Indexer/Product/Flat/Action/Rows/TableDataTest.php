<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Action\Rows;

use Magento\Catalog\Helper\Product\Flat\Indexer;
use Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows\TableData;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TableDataTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    protected $_connectionMock;

    /**
     * @var Indexer|MockObject
     */
    protected $_productIndexerHelper;

    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Resource|MockObject
     */
    protected $_resourceMock;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->_resourceMock = $this->createMock(ResourceConnection::class);
        $this->_productIndexerHelper = $this->createMock(Indexer::class);
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
        )->willReturn(
            false
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
        )->willReturn(
            $this->_connectionMock
        );

        $model = $this->_objectManager->getObject(
            TableData::class,
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

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects(
            $this->once()
        )->method(
            'from'
        )->with(
            ['tf' => sprintf('%s_tmp_indexer', $flatTable)],
            ['column_2', 'column_3']
        );
        $sql = hash('md5', (string)time());
        $selectMock->expects(
            $this->once()
        )->method(
            'insertFromSelect'
        )->with(
            $flatTable,
            ['column_2', 'column_3']
        )->willReturn(
            $sql
        );

        $this->_connectionMock->expects($this->once())->method('query')->with($sql);

        $this->_connectionMock->expects($this->once())->method('select')->willReturn($selectMock);

        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'isTableExists'
        )->with(
            $flatTable
        )->willReturn(
            true
        );

        $this->_connectionMock->expects(
            $this->once()
        )->method(
            'describeTable'
        )->with(
            $flatTable
        )->willReturn(
            $describedColumns
        );

        $this->_productIndexerHelper->expects(
            $this->once()
        )->method(
            'getFlatColumns'
        )->willReturn(
            $flatColumns
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
        )->willReturn(
            $this->_connectionMock
        );

        $model = $this->_objectManager->getObject(
            TableData::class,
            ['resource' => $this->_resourceMock, 'productIndexerHelper' => $this->_productIndexerHelper]
        );

        $model->move($flatTable, $flatDropName, $temporaryFlatTableName);
    }
}

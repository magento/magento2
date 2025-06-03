<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\Table\StrategyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractResourceTest extends TestCase
{
    /**
     * @var AbstractResourceStub
     */
    protected $model;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $_resourceMock;

    /**
     * @var StrategyInterface|MockObject
     */
    protected $_tableStrategyInterface;

    protected function setUp(): void
    {
        $this->_resourceMock = $this->getMockBuilder(
            ResourceConnection::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->_tableStrategyInterface = $this->getMockForAbstractClass(StrategyInterface::class);
        $objectManager = new ObjectManager($this);
        $arguments = $objectManager->getConstructArguments(
            AbstractResourceStub::class,
            [
                'resource' => $this->_resourceMock,
                'tableStrategy' => $this->_tableStrategyInterface
            ]
        );
        $this->model = $objectManager->getObject(
            AbstractResourceStub::class,
            $arguments
        );
    }

    public function testReindexAll()
    {
        $this->_tableStrategyInterface->expects($this->once())
            ->method('setUseIdxTable')
            ->with(true);
        $this->_tableStrategyInterface->expects($this->once())
            ->method('prepareTableName')
            ->with('test')
            ->willReturn('test_idx');
        $this->model->reindexAll();
        $this->assertEquals('test_idx', $this->model->getIdxTable('test'));
    }

    public function testClearTemporaryIndexTable()
    {
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->_resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $connectionMock->expects($this->once())->method('delete')->willReturnSelf();
        $this->model->clearTemporaryIndexTable();
    }

    public function testSyncData()
    {
        $resultTable = 'catalog_category_flat';
        $resultColumns = [0 => 'column'];
        $describeTable = ['column' => 'column'];

        $selectMock = $this->createMock(Select::class);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $connectionMock->expects($this->any())->method('describeTable')->willReturn($describeTable);
        $connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();

        $selectMock->expects($this->once())->method('insertFromSelect')->with(
            $resultTable,
            $resultColumns
        )->willReturnSelf();

        $this->_resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $this->_resourceMock->expects($this->any())->method('getTableName')->willReturnArgument(0);

        $this->assertInstanceOf(
            AbstractResourceStub::class,
            $this->model->syncData()
        );
    }

    public function testSyncDataException()
    {
        $this->expectException('Exception');
        $describeTable = ['column' => 'column'];
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $connectionMock->expects($this->any())->method('describeTable')->willReturn($describeTable);
        $connectionMock->expects($this->any())->method('select')->willThrowException(new \Exception());
        $this->_resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $this->_resourceMock->expects($this->any())->method('getTableName')->willReturnArgument(0);
        $connectionMock->expects($this->once())->method('rollback');
        $this->model->syncData();
    }

    /**
     * @param bool $readToIndex
     * @dataProvider insertFromTableData
     */
    public function testInsertFromTable($readToIndex)
    {
        $sourceTable = 'catalog_category_flat';
        $destTable = 'catalog_category_flat';
        $resultColumns = [0 => 'column'];
        $tableColumns = ['column' => 'column'];

        $selectMock = $this->createMock(Select::class);
        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $connectionMock->expects($this->any())->method('describeTable')->willReturn($tableColumns);
        $connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();

        if ($readToIndex) {
            $connectionCustomMock = $this->getMockBuilder(AdapterInterface::class)
                ->onlyMethods(['describeTable', 'query', 'select', 'insertArray'])
                ->getMockForAbstractClass();
            $pdoMock = $this->createMock(\Zend_Db_Statement_Pdo::class);
            $connectionCustomMock->expects($this->any())->method('query')->willReturn($selectMock);
            $connectionCustomMock->expects($this->any())->method('select')->willReturn($selectMock);
            $connectionCustomMock->expects($this->any())->method('describeTable')->willReturn(
                $tableColumns
            );
            $connectionCustomMock->expects($this->any())->method('insertArray')->with(
                $destTable,
                $resultColumns
            )->willReturn(1);
            $connectionMock->expects($this->any())->method('query')->willReturn($pdoMock);
            $pdoMock->expects($this->any())->method('fetch')->willReturn([$tableColumns]);

            $this->model->newIndexAdapter();
            $this->_resourceMock->expects($this->any())->method('getConnection')->willReturn(
                $connectionMock
            );
        } else {
            $selectMock->expects($this->once())->method('insertFromSelect')->with(
                $destTable,
                $resultColumns
            )->willReturnSelf();

            $this->_resourceMock->expects($this->any())->method('getTableName')->willReturnArgument(0);
            $this->_resourceMock->expects($this->any())->method('getConnection')->willReturn(
                $connectionMock
            );
        }
        $this->assertInstanceOf(
            AbstractResourceStub::class,
            $this->model->insertFromTable($sourceTable, $destTable, $readToIndex)
        );
    }

    /**
     * @return array
     */
    public static function insertFromTableData()
    {
        return [[false], [true]];
    }
}

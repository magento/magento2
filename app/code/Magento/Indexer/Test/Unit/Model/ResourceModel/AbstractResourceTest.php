<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\ResourceModel;

class AbstractResourceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Indexer\Test\Unit\Model\ResourceModel\AbstractResourceStub
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Framework\Indexer\Table\StrategyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_tableStrategyInterface;

    protected function setUp(): void
    {
        $this->_resourceMock = $this->getMockBuilder(
            \Magento\Framework\App\ResourceConnection::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->_tableStrategyInterface = $this->createMock(\Magento\Framework\Indexer\Table\StrategyInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = $objectManager->getConstructArguments(
            \Magento\Indexer\Test\Unit\Model\ResourceModel\AbstractResourceStub::class,
            [
                'resource' => $this->_resourceMock,
                'tableStrategy' => $this->_tableStrategyInterface
            ]
        );
        $this->model = $objectManager->getObject(
            \Magento\Indexer\Test\Unit\Model\ResourceModel\AbstractResourceStub::class,
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
        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->_resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $connectionMock->expects($this->once())->method('delete')->willReturnSelf();
        $this->model->clearTemporaryIndexTable();
    }

    public function testSyncData()
    {
        $resultTable = 'catalog_category_flat';
        $resultColumns = [0 => 'column'];
        $describeTable = ['column' => 'column'];

        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);

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
            \Magento\Indexer\Test\Unit\Model\ResourceModel\AbstractResourceStub::class,
            $this->model->syncData()
        );
    }

    /**
     */
    public function testSyncDataException()
    {
        $this->expectException(\Exception::class);

        $describeTable = ['column' => 'column'];
        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $connectionMock->expects($this->any())->method('describeTable')->willReturn($describeTable);
        $connectionMock->expects($this->any())->method('select')->will($this->throwException(new \Exception()));
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

        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->expects($this->any())->method('describeTable')->willReturn($tableColumns);
        $connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();

        if ($readToIndex) {
            $connectionCustomMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
                ->setMethods(['describeTable', 'query', 'select', 'insertArray'])
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
            \Magento\Indexer\Test\Unit\Model\ResourceModel\AbstractResourceStub::class,
            $this->model->insertFromTable($sourceTable, $destTable, $readToIndex)
        );
    }

    /**
     * @return array
     */
    public function insertFromTableData()
    {
        return [[false], [true]];
    }
}

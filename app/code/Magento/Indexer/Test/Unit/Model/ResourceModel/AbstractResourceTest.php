<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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

        $this->_tableStrategyInterface = $this->createMock(StrategyInterface::class);
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
            ->will($this->returnValue('test_idx'));
        $this->model->reindexAll();
        $this->assertEquals('test_idx', $this->model->getIdxTable('test'));
    }

    public function testClearTemporaryIndexTable()
    {
        $connectionMock = $this->createMock(AdapterInterface::class);
        $this->_resourceMock->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));
        $connectionMock->expects($this->once())->method('delete')->will($this->returnSelf());
        $this->model->clearTemporaryIndexTable();
    }

    public function testSyncData()
    {
        $resultTable = 'catalog_category_flat';
        $resultColumns = [0 => 'column'];
        $describeTable = ['column' => 'column'];

        $selectMock = $this->createMock(Select::class);
        $connectionMock = $this->createMock(AdapterInterface::class);

        $connectionMock->expects($this->any())->method('describeTable')->will($this->returnValue($describeTable));
        $connectionMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));
        $selectMock->expects($this->any())->method('from')->will($this->returnSelf());

        $selectMock->expects($this->once())->method('insertFromSelect')->with(
            $resultTable,
            $resultColumns
        )->will($this->returnSelf());

        $this->_resourceMock->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));
        $this->_resourceMock->expects($this->any())->method('getTableName')->will($this->returnArgument(0));

        $this->assertInstanceOf(
            AbstractResourceStub::class,
            $this->model->syncData()
        );
    }

    public function testSyncDataException()
    {
        $this->expectException('Exception');
        $describeTable = ['column' => 'column'];
        $connectionMock = $this->createMock(AdapterInterface::class);
        $connectionMock->expects($this->any())->method('describeTable')->will($this->returnValue($describeTable));
        $connectionMock->expects($this->any())->method('select')->will($this->throwException(new \Exception()));
        $this->_resourceMock->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));
        $this->_resourceMock->expects($this->any())->method('getTableName')->will($this->returnArgument(0));
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
            ->getMock();

        $connectionMock->expects($this->any())->method('describeTable')->will($this->returnValue($tableColumns));
        $connectionMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));
        $selectMock->expects($this->any())->method('from')->will($this->returnSelf());

        if ($readToIndex) {
            $connectionCustomMock = $this->getMockBuilder(AdapterInterface::class)
                ->setMethods(['describeTable', 'query', 'select', 'insertArray'])
                ->getMockForAbstractClass();
            $pdoMock = $this->createMock(\Zend_Db_Statement_Pdo::class);
            $connectionCustomMock->expects($this->any())->method('query')->will($this->returnValue($selectMock));
            $connectionCustomMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));
            $connectionCustomMock->expects($this->any())->method('describeTable')->will(
                $this->returnValue($tableColumns)
            );
            $connectionCustomMock->expects($this->any())->method('insertArray')->with(
                $destTable,
                $resultColumns
            )->will($this->returnValue(1));
            $connectionMock->expects($this->any())->method('query')->will($this->returnValue($pdoMock));
            $pdoMock->expects($this->any())->method('fetch')->will($this->returnValue([$tableColumns]));

            $this->model->newIndexAdapter();
            $this->_resourceMock->expects($this->any())->method('getConnection')->will(
                $this->returnValue($connectionMock)
            );
        } else {
            $selectMock->expects($this->once())->method('insertFromSelect')->with(
                $destTable,
                $resultColumns
            )->will($this->returnSelf());

            $this->_resourceMock->expects($this->any())->method('getTableName')->will($this->returnArgument(0));
            $this->_resourceMock->expects($this->any())->method('getConnection')->will(
                $this->returnValue($connectionMock)
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
    public function insertFromTableData()
    {
        return [[false], [true]];
    }
}

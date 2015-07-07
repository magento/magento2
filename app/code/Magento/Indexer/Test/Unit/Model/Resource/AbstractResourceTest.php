<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\Resource;

class AbstractResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Test\Unit\Model\Resource\AbstractResourceStub
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Indexer\Model\Indexer\Table\StrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_tableStrategyInterface;


    protected function setUp()
    {
        $this->_resourceMock = $this->getMockBuilder('Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_tableStrategyInterface = $this->getMock(
            'Magento\Indexer\Model\Indexer\Table\StrategyInterface',
            [],
            [],
            '',
            false
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = $objectManager->getConstructArguments(
            '\Magento\Indexer\Test\Unit\Model\Resource\AbstractResourceStub',
            [
                'resource' => $this->_resourceMock,
                'tableStrategy' => $this->_tableStrategyInterface
            ]
        );
        $this->model = $objectManager->getObject(
            '\Magento\Indexer\Test\Unit\Model\Resource\AbstractResourceStub',
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
        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $this->_resourceMock->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));
        $connectionMock->expects($this->once())->method('delete')->will($this->returnSelf());
        $this->model->clearTemporaryIndexTable();
    }

    public function testSyncData()
    {
        $resultTable = 'catalog_category_flat';
        $resultColumns = [0 => 'column'];
        $describeTable = ['column' => 'column'];

        $selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);

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
            'Magento\Indexer\Test\Unit\Model\Resource\AbstractResourceStub',
            $this->model->syncData()
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testSyncDataException()
    {
        $describeTable = ['column' => 'column'];
        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
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

        $selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);

        $connectionMock->expects($this->any())->method('describeTable')->will($this->returnValue($tableColumns));
        $connectionMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));
        $selectMock->expects($this->any())->method('from')->will($this->returnSelf());

        if ($readToIndex) {
            $connectionCustomMock = $this->getMock(
                'Magento\Framework\DB\Adapter\CustomAdapterInterface',
                ['describeTable', 'query', 'select', 'insertArray'],
                [],
                '',
                false
            );
            $pdoMock = $this->getMock('Zend_Db_Statement_Pdo', [], [], '', false);
            $connectionCustomMock->expects($this->any())->method('query')->will($this->returnValue($selectMock));
            $connectionCustomMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));
            $connectionCustomMock->expects($this->any())->method('describeTable')->will(
                $this->returnValue($tableColumns)
            );
            $connectionCustomMock->expects($this->exactly(1))->method('insertArray')->with(
                $destTable,
                $resultColumns
            )->will($this->returnValue(1));
            $connectionMock->expects($this->any())->method('query')->will($this->returnValue($pdoMock));
            $pdoMock->expects($this->at(0))->method('fetch')->will($this->returnValue([$tableColumns]));
            $pdoMock->expects($this->at(1))->method('fetch')->will($this->returnValue([$tableColumns]));

            $this->model->newIndexAdapter();
            $this->_resourceMock->expects($this->at(0))->method('getConnection')->with('core_write')->will(
                $this->returnValue($connectionMock)
            );
            $this->_resourceMock->expects($this->at(1))->method('getConnection')->with('core_new_write')->will(
                $this->returnValue($connectionCustomMock)
            );
        } else {
            $selectMock->expects($this->once())->method('insertFromSelect')->with(
                $destTable,
                $resultColumns
            )->will($this->returnSelf());

            $this->_resourceMock->expects($this->any())->method('getTableName')->will($this->returnArgument(0));
            $this->_resourceMock->expects($this->any())->method('getConnection')->with('core_write')->will(
                $this->returnValue($connectionMock)
            );
        }
        $this->assertInstanceOf(
            'Magento\Indexer\Test\Unit\Model\Resource\AbstractResourceStub',
            $this->model->insertFromTable($sourceTable, $destTable, $readToIndex)
        );
    }

    public function insertFromTableData()
    {
        return [[false], [true]];
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Test\Unit\Model\ResourceModel;

use Magento\Framework\Indexer\StateInterface;

class FrontendResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\ResourceModel\FrontendResource
     */
    private $model;

    /**
     * @var string
     */
    private $indexerId;

    /**
     * @var string
     */
    private $indexerBaseTable;

    /**
     * @var \Magento\Indexer\Model\Indexer\StateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateFactoryMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var string
     */
    private $connectionName;

    protected function setUp()
    {
        $this->indexerId = 'indexer_id';
        $this->indexerBaseTable = 'indexer_base_table';
        $this->connectionName = 'connectionName';

        $this->stateFactoryMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\StateFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->resourceMock = $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false);
        $contextMock = $this->getMock(\Magento\Framework\Model\ResourceModel\Db\Context::class, [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->model = new \Magento\Indexer\Model\ResourceModel\FrontendResource(
            $contextMock,
            $this->indexerId,
            $this->indexerBaseTable,
            'idFieldName',
            $this->stateFactoryMock,
            $this->connectionName
        );
    }

    public function testGetMainTable()
    {
        $mainTable = $this->indexerBaseTable . StateInterface::ADDITIONAL_TABLE_SUFFIX;
        $stateMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['loadByIndexer', 'getTableSuffix'],
            [],
            '',
            false
        );
        $this->stateFactoryMock->expects($this->once())->method('create')->willReturn($stateMock);

        $stateMock->expects($this->once())->method('loadByIndexer')->with($this->indexerId)->willReturnSelf();
        $stateMock->expects($this->once())
            ->method('getTableSuffix')
            ->willReturn(StateInterface::ADDITIONAL_TABLE_SUFFIX);

        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->with($mainTable, $this->connectionName)
            ->willReturn($mainTable);

        $this->assertEquals(
            $mainTable,
            $this->model->getMainTable()
        );
    }
}

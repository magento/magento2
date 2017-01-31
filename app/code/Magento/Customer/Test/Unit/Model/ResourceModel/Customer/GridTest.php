<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\ResourceModel\Customer;

use Magento\Customer\Model\ResourceModel\Customer\Grid;

class GridTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $indexerRegistry;

    /** @var \Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $flatScopeResolver;

    /** @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $indexer;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $connection;

    /** @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject */
    protected $select;

    /** @var Grid */
    protected $observer;

    /** @var \Zend_Db_Statement_Interface|\PHPUnit_Framework_MockObject_MockObject */
    protected $queryResult;

    protected function setUp()
    {
        $this->resource = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $this->indexerRegistry = $this->getMock('Magento\Framework\Indexer\IndexerRegistry', [], [], '', false);
        $this->flatScopeResolver = $this->getMock(
            'Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver',
            [],
            [],
            '',
            false
        );
        $this->indexer = $this->getMockForAbstractClass(
            'Magento\Framework\Indexer\IndexerInterface',
            [],
            '',
            false
        );
        $this->connection = $this->getMockForAbstractClass(
            'Magento\Framework\DB\Adapter\AdapterInterface',
            [],
            '',
            false
        );
        $this->select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $this->queryResult = $this->getMockForAbstractClass(
            'Zend_Db_Statement_Interface',
            [],
            '',
            false
        );

        $this->observer = new Grid(
            $this->resource,
            $this->indexerRegistry,
            $this->flatScopeResolver
        );
    }

    public function testSyncCustomerGrid()
    {
        $gridTable = 'customer_grid_flat';
        $customerLogTable = 'customer_log';

        $this->indexerRegistry->expects($this->once())
            ->method('get')
            ->with(\Magento\Customer\Model\Customer::CUSTOMER_GRID_INDEXER_ID)
            ->willReturn($this->indexer);
        $this->resource
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->flatScopeResolver
            ->expects($this->once())
            ->method('resolve')
            ->with(\Magento\Customer\Model\Customer::CUSTOMER_GRID_INDEXER_ID, [])
            ->willReturn($gridTable);

        $this->resource->expects($this->exactly(2))
            ->method('getTableName')
            ->willReturnMap([
                [$gridTable],
                [$customerLogTable],
            ]);

        $this->connection->expects($this->exactly(2))
            ->method('select')
            ->willReturn($this->select);
        $this->select->expects($this->exactly(2))
            ->method('from')
            ->willReturnSelf();
        $this->select->expects($this->once())
            ->method('order')
            ->with('last_visit_at DESC')
            ->willReturnSelf();
        $this->select->expects($this->once())
            ->method('limit')
            ->with(1)
            ->willReturnSelf();
        $this->connection->expects($this->atLeastOnce())
            ->method('query')
            ->with($this->select)
            ->willReturn($this->queryResult);
        $this->queryResult->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('2015-08-13 10:36:44');

        $this->select->expects($this->once())
            ->method('where')
            ->with('last_login_at > ?', '2015-08-13 10:36:44')
            ->willReturnSelf();
        $this->queryResult->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['customer_id' => 23], ['customer_id' => 65]]);
        $this->indexer->expects($this->once())
            ->method('reindexList')
            ->with(['23', '65']);

        $this->observer->syncCustomerGrid();
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\ResourceModel\Customer;

use Magento\Customer\Model\ResourceModel\Customer\Grid;

class GridTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject */
    protected $resource;

    /** @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    protected $indexerRegistry;

    /** @var \Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver|\PHPUnit\Framework\MockObject\MockObject */
    protected $flatScopeResolver;

    /** @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $indexer;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $connection;

    /** @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject */
    protected $select;

    /** @var Grid */
    protected $observer;

    /** @var \Zend_Db_Statement_Interface|\PHPUnit\Framework\MockObject\MockObject */
    protected $queryResult;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->indexerRegistry = $this->createMock(\Magento\Framework\Indexer\IndexerRegistry::class);
        $this->flatScopeResolver = $this->createMock(\Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver::class);
        $this->indexer = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\IndexerInterface::class,
            [],
            '',
            false
        );
        $this->connection = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false
        );
        $this->select = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->queryResult = $this->getMockForAbstractClass(
            \Zend_Db_Statement_Interface::class,
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

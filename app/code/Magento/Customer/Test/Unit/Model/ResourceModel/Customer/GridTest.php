<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Customer;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\Grid;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GridTest extends TestCase
{
    /** @var ResourceConnection|MockObject */
    protected $resource;

    /** @var IndexerRegistry|MockObject */
    protected $indexerRegistry;

    /** @var FlatScopeResolver|MockObject */
    protected $flatScopeResolver;

    /** @var IndexerInterface|MockObject */
    protected $indexer;

    /** @var AdapterInterface|MockObject */
    protected $connection;

    /** @var Select|MockObject */
    protected $select;

    /** @var Grid */
    protected $observer;

    /** @var \Zend_Db_Statement_Interface|MockObject */
    protected $queryResult;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->indexerRegistry = $this->createMock(IndexerRegistry::class);
        $this->flatScopeResolver = $this->createMock(FlatScopeResolver::class);
        $this->indexer = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false
        );
        $this->connection = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false
        );
        $this->select = $this->createMock(Select::class);
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
            ->with(Customer::CUSTOMER_GRID_INDEXER_ID)
            ->willReturn($this->indexer);
        $this->resource
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->flatScopeResolver
            ->expects($this->once())
            ->method('resolve')
            ->with(Customer::CUSTOMER_GRID_INDEXER_ID, [])
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

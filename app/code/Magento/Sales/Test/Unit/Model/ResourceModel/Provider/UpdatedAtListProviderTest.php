<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Provider;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Sales\Model\Grid\LastUpdateTimeCache;
use Magento\Sales\Model\ResourceModel\Provider\UpdatedAtListProvider;
use PHPUnit\Framework\TestCase;

class UpdatedAtListProviderTest extends TestCase
{
    public function testGetIdsWithCutoffDoesNotExcludeTimestampMismatchesBehindWatermark(): void
    {
        $cutoff = '2026-01-01 12:00:00';
        $select = $this->createMock(Select::class);
        $select->expects($this->once())
            ->method('from')
            ->with(['main_table' => 'prefix_sales_order'], ['main_table.entity_id'])
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('joinInner')
            ->with(
                ['grid_table' => 'prefix_sales_order_grid'],
                'main_table.entity_id = grid_table.entity_id'
                . ' AND (main_table.updated_at <> grid_table.updated_at OR grid_table.updated_at IS NULL)',
                []
            )
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('where')
            ->with('main_table.updated_at <= ?', $cutoff)
            ->willReturnSelf();

        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('select')->willReturn($select);
        $connection->expects($this->once())
            ->method('fetchAll')
            ->with($select, [], \Zend_Db::FETCH_COLUMN)
            ->willReturn(['42']);
        $resourceConnection = $this->createStub(ResourceConnection::class);
        $resourceConnection->method('getConnection')->willReturn($connection);
        $resourceConnection->method('getTableName')
            ->willReturnCallback(static fn(string $table): string => 'prefix_' . $table);

        $provider = new UpdatedAtListProvider($resourceConnection);

        self::assertSame(['42'], $provider->getIdsWithCutoff('sales_order', 'sales_order_grid', $cutoff));
    }
}

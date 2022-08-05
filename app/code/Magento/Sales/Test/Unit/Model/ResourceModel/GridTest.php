<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface as ConnectionAdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Grid\LastUpdateTimeCache;
use Magento\Sales\Model\ResourceModel\Grid;
use Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Sales\Model\ResourceModel\Grid class
 */
class GridTest extends TestCase
{
    /**
     * @var Grid
     */
    private $grid;

    /**
     * @var NotSyncedDataProviderInterface|MockObject
     */
    private $notSyncedDataProvider;

    /**
     * @var ConnectionAdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var LastUpdateTimeCache|MockObject
     */
    private $lastUpdateTimeCache;

    /**
     * @var string
     */
    private $mainTable = 'sales_order';

    /**
     * @var string
     */
    private $gridTable = 'sales_order_grid';

    /**
     * @var array
     */
    private $columns = [
        'entity_id' => 'sales_order.entity_id',
        'status' => 'sales_order.status',
        'updated_at' => 'sales_order.updated_at',
    ];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->notSyncedDataProvider = $this->createMock(NotSyncedDataProviderInterface::class);
        $this->connection = $this->createMock(ConnectionAdapterInterface::class);
        $this->lastUpdateTimeCache = $this->createMock(LastUpdateTimeCache::class);

        $this->grid = $objectManager->getObject(
            Grid::class,
            [
                'notSyncedDataProvider' => $this->notSyncedDataProvider,
                'mainTableName' => $this->mainTable,
                'gridTableName' => $this->gridTable,
                'connection' => $this->connection,
                '_tables' => ['sales_order' => $this->mainTable, 'sales_order_grid' => $this->gridTable],
                'columns' => $this->columns,
                'lastUpdateTimeCache' => $this->lastUpdateTimeCache,
            ]
        );
    }

    /**
     * Test for refreshBySchedule() method
     */
    public function testRefreshBySchedule()
    {
        $notSyncedIds = ['1', '2', '3'];
        $fetchResult = [];
        for ($i = 1; $i <= 220; $i++) {
            $fetchResult[] = [
                'entity_id' => $i,
                'status' => 1,
                'updated_at' => '2021-01-01 01:02:03',
            ];
        }
        $fetchResult[50]['updated_at'] = '2021-02-03 01:02:03';
        $fetchResult[150]['updated_at'] = '2021-03-04 01:02:03';

        $this->notSyncedDataProvider->expects($this->atLeastOnce())
            ->method('getIds')
            ->willReturn($notSyncedIds);
        $select = $this->createMock(Select::class);
        $select->expects($this->atLeastOnce())
            ->method('from')
            ->with(['sales_order' => $this->mainTable], [])
            ->willReturnSelf();
        $select->expects($this->atLeastOnce())
            ->method('columns')
            ->willReturnSelf();
        $select->expects($this->atLeastOnce())
            ->method('where')
            ->with($this->mainTable . '.entity_id IN (?)', $notSyncedIds)
            ->willReturnSelf();

        $this->connection->expects($this->atLeastOnce())
            ->method('select')
            ->willReturn($select);
        $this->connection->expects($this->atLeastOnce())
            ->method('fetchAll')
            ->with($select)
            ->willReturn($fetchResult);
        $this->connection->expects($this->atLeastOnce())
            ->method('insertOnDuplicate')
            ->with($this->gridTable, $fetchResult, array_keys($this->columns))
            ->willReturn(array_count_values($notSyncedIds));

        $this->lastUpdateTimeCache->expects($this->once())
            ->method('save')
            ->with($this->gridTable, '2021-03-04 01:02:03');

        $this->grid->refreshBySchedule();
    }
}

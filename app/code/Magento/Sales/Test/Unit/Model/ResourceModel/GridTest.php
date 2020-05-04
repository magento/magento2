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
        'column_1_key' => 'column_1_value',
        'column_2_key' => 'column_2_value'
    ];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->notSyncedDataProvider = $this->getMockBuilder(NotSyncedDataProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIds'])
            ->getMockForAbstractClass();
        $this->connection = $this->getMockBuilder(ConnectionAdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['select', 'fetchAll', 'insertOnDuplicate'])
            ->getMockForAbstractClass();

        $this->grid = $objectManager->getObject(
            Grid::class,
            [
                'notSyncedDataProvider' => $this->notSyncedDataProvider,
                'mainTableName' => $this->mainTable,
                'gridTableName' => $this->gridTable,
                'connection' => $this->connection,
                '_tables' => ['sales_order' => $this->mainTable, 'sales_order_grid' => $this->gridTable],
                'columns' => $this->columns
            ]
        );
    }

    /**
     * Test for refreshBySchedule() method
     */
    public function testRefreshBySchedule()
    {
        $notSyncedIds = ['1', '2', '3'];
        $fetchResult = ['column_1' => '1', 'column_2' => '2'];

        $this->notSyncedDataProvider->expects($this->atLeastOnce())->method('getIds')->willReturn($notSyncedIds);
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['from', 'columns', 'where'])
            ->getMock();
        $select->expects($this->atLeastOnce())->method('from')->with(['sales_order' => $this->mainTable], [])
            ->willReturnSelf();
        $select->expects($this->atLeastOnce())->method('columns')->willReturnSelf();
        $select->expects($this->atLeastOnce())->method('where')
            ->with($this->mainTable . '.entity_id IN (?)', $notSyncedIds)
            ->willReturnSelf();

        $this->connection->expects($this->atLeastOnce())->method('select')->willReturn($select);
        $this->connection->expects($this->atLeastOnce())->method('fetchAll')->with($select)->willReturn($fetchResult);
        $this->connection->expects($this->atLeastOnce())->method('insertOnDuplicate')
            ->with($this->gridTable, $fetchResult, array_keys($this->columns))
            ->willReturn(array_count_values($notSyncedIds));

        $this->grid->refreshBySchedule();
    }
}

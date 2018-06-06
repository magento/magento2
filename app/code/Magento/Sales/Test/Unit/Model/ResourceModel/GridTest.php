<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProviderInterface;
use Magento\Framework\DB\Adapter\AdapterInterface as ConnectionAdapterInterface;
use Magento\Sales\Model\ResourceModel\Grid;

/**
 * Unit tests for \Magento\Sales\Model\ResourceModel\Grid class
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Grid
     */
    private $grid;

    /**
     * @var NotSyncedDataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notSyncedDataProvider;

    /**
     * @var ConnectionAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
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
            \Magento\Sales\Model\ResourceModel\Grid::class,
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
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
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

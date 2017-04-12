<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Order;

use Magento\Reports\Model\ResourceModel\Order\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Order\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\EntitySnapshot|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entitySnapshotMock;

    /**
     * @var \Magento\Framework\DB\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timezoneMock;

    /**
     * @var \Magento\Sales\Model\Order\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\OrderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\EntityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMock();
        $this->fetchStrategyMock = $this->getMockBuilder(
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class
        )->getMock();
        $this->managerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->getMock();
        $snapshotClassName = \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class;
        $this->entitySnapshotMock = $this->getMockBuilder($snapshotClassName)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder(\Magento\Framework\DB\Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMock();
        $this->timezoneMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->getMock();
        $this->configMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderFactoryMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Report\OrderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock
            ->expects($this->any())
            ->method('columns')
            ->willReturnSelf();
        $this->selectMock
            ->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $this->selectMock
            ->expects($this->any())
            ->method('order')
            ->willReturnSelf();
        $this->selectMock
            ->expects($this->any())
            ->method('group')
            ->willReturnSelf();
        $this->selectMock
            ->expects($this->any())
            ->method('getPart')
            ->willReturn([]);

        $this->connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->setMethods(['select', 'getIfNullSql', 'getDateFormatSql', 'prepareSqlCondition', 'getCheckSql'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock
            ->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->collection = new Collection(
            $this->entityFactoryMock,
            $this->loggerMock,
            $this->fetchStrategyMock,
            $this->managerMock,
            $this->entitySnapshotMock,
            $this->helperMock,
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->timezoneMock,
            $this->configMock,
            $this->orderFactoryMock,
            null,
            $this->resourceMock
        );
    }

    /**
     * @return void
     */
    public function testCheckIsLive()
    {
        $range = '';
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'sales/dashboard/use_aggregated_data',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

        $this->collection->checkIsLive($range);
    }

    /**
     * @param int $useAggregatedData
     * @param string $mainTable
     * @param int $isFilter
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $getIfNullSqlResult
     * @dataProvider useAggregatedDataDataProvider
     * @return void
     */
    public function testPrepareSummary($useAggregatedData, $mainTable, $isFilter, $getIfNullSqlResult)
    {
        $range = '';
        $customStart = 1;
        $customEnd = 10;

        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'sales/dashboard/use_aggregated_data',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn($useAggregatedData);

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Report\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($orderMock);

        $this->resourceMock
            ->expects($this->at(0))
            ->method('getTable')
            ->with($mainTable);

        $this->connectionMock
            ->expects($getIfNullSqlResult)
            ->method('getIfNullSql');

        $this->collection->prepareSummary($range, $customStart, $customEnd, $isFilter);
    }

    /**
     * @param int $range
     * @param string $customStart
     * @param string $customEnd
     * @param string $expectedInterval
     * @dataProvider firstPartDateRangeDataProvider
     * @return void
     */
    public function testGetDateRangeFirstPart($range, $customStart, $customEnd, $expectedInterval)
    {
        $timeZoneToReturn = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $result = $this->collection->getDateRange($range, $customStart, $customEnd);
        $interval = $result['to']->diff($result['from']);
        date_default_timezone_set($timeZoneToReturn);
        $intervalResult = $interval->format('%y %m %d %h:%i:%s');
        $this->assertEquals($expectedInterval, $intervalResult);
    }

    /**
     * @param int $range
     * @param string $customStart
     * @param string $customEnd
     * @param string $config
     * @dataProvider secondPartDateRangeDataProvider
     * @return void
     */
    public function testGetDateRangeSecondPart($range, $customStart, $customEnd, $config)
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                $config,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn(1);

        $result = $this->collection->getDateRange($range, $customStart, $customEnd);
        $this->assertEquals(3, count($result));
    }

    /**
     * @return void
     */
    public function testGetDateRangeWithReturnObject()
    {
        $this->assertEquals(2, count($this->collection->getDateRange('7d', '', '', true)));
        $this->assertEquals(3, count($this->collection->getDateRange('7d', '', '', false)));
    }

    /**
     * @return void
     */
    public function testAddItemCountExpr()
    {
        $this->selectMock
            ->expects($this->once())
            ->method('columns')
            ->with(['items_count' => 'total_item_count'], 'main_table');
        $this->collection->addItemCountExpr();
    }

    /**
     * @param int $isFilter
     * @param int $useAggregatedData
     * @param string $mainTable
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $getIfNullSqlResult
     * @dataProvider totalsDataProvider
     * @return void
     */
    public function testCalculateTotals($isFilter, $useAggregatedData, $mainTable, $getIfNullSqlResult)
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'sales/dashboard/use_aggregated_data',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn($useAggregatedData);

        $this->resourceMock
            ->expects($this->at(0))
            ->method('getTable')
            ->with($mainTable);

        $this->connectionMock
            ->expects($getIfNullSqlResult)
            ->method('getIfNullSql');

        $this->collection->checkIsLive('');
        $this->collection->calculateTotals($isFilter);
    }

    /**
     * @param int $isFilter
     * @param string $useAggregatedData
     * @param string $mainTable
     * @dataProvider salesDataProvider
     * @return void
     */
    public function testCalculateSales($isFilter, $useAggregatedData, $mainTable)
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'sales/dashboard/use_aggregated_data',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn($useAggregatedData);

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->resourceMock
            ->expects($this->at(0))
            ->method('getTable')
            ->with($mainTable);

        $this->collection->calculateSales($isFilter);
    }

    /**
     * @return void
     */
    public function testSetDateRange()
    {
        $fromDate = '1';
        $toDate = '2';

        $this->connectionMock
            ->expects($this->at(0))
            ->method('prepareSqlCondition')
            ->with('`created_at`', ['from' => $fromDate, 'to' => $toDate]);

        $this->collection->setDateRange($fromDate, $toDate);
    }

    /**
     * @param array $storeIds
     * @param array $parameters
     * @dataProvider storesDataProvider
     * @return void
     */
    public function testSetStoreIds($storeIds, $parameters)
    {
        $this->connectionMock
            ->expects($this->any())
            ->method('getIfNullSql')
            ->willReturn('text');

        $this->selectMock
            ->expects($this->once())
            ->method('columns')
            ->with($parameters)
            ->willReturnSelf();

        $this->collection->setStoreIds($storeIds);
    }

    /**
     * @return array
     */
    public function useAggregatedDataDataProvider()
    {
        return [
            [1, 'sales_order_aggregated_created', 0, $this->never()],
            [0, 'sales_order', 0, $this->exactly(7)],
            [0, 'sales_order', 1, $this->exactly(6)]
        ];
    }

    /**
     * @return array
     */
    public function firstPartDateRangeDataProvider()
    {
        return [
            ['', '', '', '0 0 0 23:59:59'],
            ['24h', '', '', '0 0 1 0:0:0'],
            ['7d', '', '', '0 0 6 23:59:59']
        ];
    }

    /**
     * @return array
     */
    public function secondPartDateRangeDataProvider()
    {
        return [
            ['1m', 1, 10, 'reports/dashboard/mtd_start'],
            ['1y', 1, 10, 'reports/dashboard/ytd_start'],
            ['2y', 1, 10, 'reports/dashboard/ytd_start']
        ];
    }

    /**
     * @return array
     */
    public function totalsDataProvider()
    {
        return [
            [1, 1, 'sales_order_aggregated_created', $this->never()],
            [0, 1, 'sales_order_aggregated_created', $this->never()],
            [1, 0, 'sales_order', $this->exactly(10)],
            [0, 0, 'sales_order', $this->exactly(11)]
        ];
    }

    /**
     * @return array
     */
    public function salesDataProvider()
    {
        return [
            [1, 1, 'sales_order_aggregated_created'],
            [0, 1, 'sales_order_aggregated_created'],
            [1, 0, 'sales_order'],
            [0, 0, 'sales_order']
        ];
    }

    /**
     * @return array
     */
    public function storesDataProvider()
    {
        $firstReturn = [
            'subtotal' => 'SUM(main_table.base_subtotal * main_table.base_to_global_rate)',
            'tax' => 'SUM(main_table.base_tax_amount * main_table.base_to_global_rate)',
            'shipping' => 'SUM(main_table.base_shipping_amount * main_table.base_to_global_rate)',
            'discount' => 'SUM(main_table.base_discount_amount * main_table.base_to_global_rate)',
            'total' => 'SUM(main_table.base_grand_total * main_table.base_to_global_rate)',
            'invoiced' => 'SUM(main_table.base_total_paid * main_table.base_to_global_rate)',
            'refunded' => 'SUM(main_table.base_total_refunded * main_table.base_to_global_rate)',
            'profit' => 'SUM(text *  main_table.base_to_global_rate) + SUM(text * main_table.base_to_global_rate) '.
                '- SUM(text * main_table.base_to_global_rate) - SUM(text * main_table.base_to_global_rate) '.
                '- SUM(text * main_table.base_to_global_rate)',
        ];

        $secondReturn = [
            'subtotal' => 'SUM(main_table.base_subtotal)',
            'tax' => 'SUM(main_table.base_tax_amount)',
            'shipping' => 'SUM(main_table.base_shipping_amount)',
            'discount' => 'SUM(main_table.base_discount_amount)',
            'total' => 'SUM(main_table.base_grand_total)',
            'invoiced' => 'SUM(main_table.base_total_paid)',
            'refunded' => 'SUM(main_table.base_total_refunded)',
            'profit' => 'SUM(text) + SUM(text) - SUM(text) - SUM(text) - SUM(text)',
        ];

        return [
            [[], $firstReturn],
            [[1], $secondReturn]
        ];
    }
}

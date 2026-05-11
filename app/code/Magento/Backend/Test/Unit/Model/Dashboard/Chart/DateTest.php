<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Dashboard\Chart;

use Magento\Backend\Model\Dashboard\Chart\Date;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Helper;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;
use Magento\Backend\Model\Dashboard\Period;
use Magento\Reports\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\EntitySnapshot;
use Magento\Sales\Model\ResourceModel\Report\OrderFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DateTest extends TestCase
{
    /**
     * @var Date
     */
    private $model;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var EntityFactory|MockObject
     */
    private $entityFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    private $fetchStrategyMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $managerMock;

    /**
     * @var EntitySnapshot|MockObject
     */
    private $entitySnapshotMock;

    /**
     * @var Helper|MockObject
     */
    private $helperMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $timezoneMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var OrderFactory|MockObject
     */
    private $orderFactoryMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var AbstractDb|MockObject
     */
    private $resourceMock;

    /**
     * @var CollectionFactory
     */
    private $collectionFactoryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->collection = $this->getCollectionObject();
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->collectionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->collection);
        $this->model = new Date($this->collectionFactoryMock, $this->timezoneMock);
    }

    /**
     * @param string $period
     * @param string $config
     * @param int $expectedYear
     *
     * @return void
     */
    #[DataProvider('getByPeriodDataProvider')]
    public function testGetByPeriod($period, $config, $expectedYear): void
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                $config,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(1);
        $dates = $this->model->getByPeriod($period);
        $this->assertEquals($expectedYear, substr($dates[0], 0, 4));
    }

    /**
     * @return array
     */
    public static function getByPeriodDataProvider(): array
    {
        $dateStart = new \DateTime();
        $expectedYear = $dateStart->format('Y');
        $expected2YTDYear = $expectedYear - 1;

        return [
            [Period::PERIOD_1_YEAR, 'reports/dashboard/ytd_start', $expectedYear],
            [Period::PERIOD_2_YEARS, 'reports/dashboard/ytd_start', $expected2YTDYear]
        ];
    }

    /**
     * @return Collection
     */
    private function getCollectionObject()
    {
        $this->entityFactoryMock = $this->createMock(EntityFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->fetchStrategyMock = $this->createMock(
            FetchStrategyInterface::class
        );
        $this->managerMock = $this->createMock(ManagerInterface::class);
        $snapshotClassName = Snapshot::class;
        $this->entitySnapshotMock = $this->createMock($snapshotClassName);
        $this->helperMock = $this->createMock(Helper::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->timezoneMock = $this->createMock(TimezoneInterface::class);
        $this->timezoneMock
            ->expects($this->any())
            ->method('getConfigTimezone')
            ->willReturn('America/Chicago');
        $this->configMock = $this->createMock(Config::class);
        $this->orderFactoryMock = $this->createPartialMock(OrderFactory::class, ['create']);

        $this->selectMock = $this->createMock(Select::class);
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
        $this->connectionMock = $this->createPartialMock(
            Mysql::class,
            ['select', 'getIfNullSql', 'getDateFormatSql', 'prepareSqlCondition', 'getCheckSql', 'getTableName']
        );

        $this->connectionMock
            ->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->connectionMock
            ->expects($this->any())
            ->method('getTableName')
            ->willReturnArgument(0);
        $this->resourceMock = $this->createPartialMock(
            AbstractDb::class,
            ['getConnection', 'getMainTable', 'getTable', '_construct']
        );
        $this->resourceMock
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceMock
            ->expects($this->any())
            ->method('getMainTable')
            ->willReturn('sales_order');
        $this->resourceMock
            ->expects($this->any())
            ->method('getTable')
            ->willReturnArgument(0);
        return new Collection(
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
}

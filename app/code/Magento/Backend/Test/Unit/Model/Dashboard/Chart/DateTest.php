<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
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
use Magento\Sales\Model\ResourceModel\Report\OrderFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var \Magento\Sales\Model\ResourceModel\EntitySnapshot|MockObject
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
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
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
     * @dataProvider getByPeriodDataProvider
     */
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
        $this->entityFactoryMock = $this->getMockBuilder(EntityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->fetchStrategyMock = $this->getMockBuilder(
            FetchStrategyInterface::class
        )->getMock();
        $this->managerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();
        $snapshotClassName = Snapshot::class;
        $this->entitySnapshotMock = $this->getMockBuilder($snapshotClassName)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->timezoneMock = $this->getMockBuilder(TimezoneInterface::class)
            ->getMock();
        $this->timezoneMock
            ->expects($this->any())
            ->method('getConfigTimezone')
            ->willReturn('America/Chicago');
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderFactoryMock = $this->getMockBuilder(OrderFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock = $this->getMockBuilder(Select::class)
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
        $this->connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['select', 'getIfNullSql', 'getDateFormatSql', 'prepareSqlCondition', 'getCheckSql'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock
            ->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->resourceMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
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

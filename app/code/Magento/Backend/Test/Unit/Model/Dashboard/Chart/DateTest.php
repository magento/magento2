<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Dashboard\Chart;

use Magento\Backend\Model\Dashboard\Chart\Date;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Helper;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;
use Magento\Backend\Model\Dashboard\Period;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Report\OrderFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DateTest extends TestCase
{
    /**
     * @var Date
     */
    private $model;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var CollectionFactory
     */
    private $collectionFactoryMock;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
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

        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->collection);

        $this->objectManagerHelper = new ObjectManager($this);

        $this->model = $this->objectManagerHelper->getObject(
            Date::class,
            [
                'collectionFactory' => $this->collectionFactoryMock,
                'localeDate' => $this->timezoneMock
            ]
        );
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
        $this->assertEquals($expectedYear, substr($dates[0],0,4));
    }

    /**
     * @return array
     */
    public function getByPeriodDataProvider(): array
    {
        $dateStart = new \DateTime();
        $expectedYear = $dateStart->format('Y');
        $expected2YTDYear = $expectedYear - 1;

        return [
            [Period::PERIOD_1_YEAR, 'reports/dashboard/ytd_start', $expectedYear],
            [Period::PERIOD_2_YEARS, 'reports/dashboard/ytd_start', $expected2YTDYear]
        ];
    }
}

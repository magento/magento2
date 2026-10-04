<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Report;

use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\ResourceModel\Report\Collection;
use Magento\Reports\Model\ResourceModel\Report\Collection\Factory as ReportCollectionFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Reports\Model\ResourceModel\Report\Collection
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var EntityFactory|MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $timezoneMock;

    /**
     * @var ReportCollectionFactory|MockObject
     */
    protected $factoryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->createMock(EntityFactory::class);
        $this->timezoneMock = $this->createMock(TimezoneInterface::class);
        $this->factoryMock = $this->createMock(ReportCollectionFactory::class);

        $this->timezoneMock->method('formatDate')
            ->willReturnCallback([$this, 'formatDate']);

        $this->collection = new Collection(
            $this->entityFactoryMock,
            $this->timezoneMock,
            $this->factoryMock
        );
    }

    /**
     * @return void
     */
    public function testGetPeriods()
    {
        $expectedArray = ['day' => 'Day', 'month' => 'Month', 'year' => 'Year'];
        $this->assertEquals($expectedArray, $this->collection->getPeriods());
    }

    /**
     * @return void
     */
    public function testGetStoreIds()
    {
        $storeIds = [1];
        $this->assertNull($this->collection->getStoreIds());
        $this->collection->setStoreIds($storeIds);
        $this->assertEquals($storeIds, $this->collection->getStoreIds());
    }

    /**
     * @param string $period
     * @param \DateTimeInterface $fromDate
     * @param \DateTimeInterface $toDate
     * @param int $size
     * @return void
     */
    #[DataProvider('intervalsDataProvider')]
    public function testGetSize($period, $fromDate, $toDate, $size)
    {
        $this->collection->setPeriod($period);
        $this->collection->setInterval($fromDate, $toDate);
        $this->assertEquals($size, $this->collection->getSize());
    }

    /**
     * @return void
     */
    public function testGetPageSize()
    {
        $pageSize = 1;
        $this->assertNull($this->collection->getPageSize());
        $this->collection->setPageSize($pageSize);
        $this->assertEquals($pageSize, $this->collection->getPageSize());
    }

    /**
     * @param string $period
     * @param \DateTimeInterface $fromDate
     * @param \DateTimeInterface $toDate
     * @param int $size
     * @return void
     */
    #[DataProvider('intervalsDataProvider')]
    public function testGetReports($period, $fromDate, $toDate, $size)
    {
        $this->collection->setPeriod($period);
        $this->collection->setInterval($fromDate, $toDate);
        $reports = $this->collection->getReports();
        foreach ($reports as $report) {
            $this->assertInstanceOf(DataObject::class, $report);
            $reportData = $report->getData();
            $this->assertEmpty($reportData['children']);
            $this->assertTrue($reportData['is_empty']);
        }
        $this->assertCount($size, $reports);
    }

    /**
     * @return void
     */
    public function testLoadData()
    {
        $this->assertInstanceOf(
            Collection::class,
            $this->collection->loadData()
        );
    }

    #[DataProvider('monthIntervalsDataProvider')]
    public function testMonthIntervalsPreserveCalendarBoundaries(string $from, string $to, array $expected): void
    {
        $this->timezoneMock->method('convertConfigTimeToUtc')->willReturnArgument(0);
        $this->collection->setPeriod('month');
        $this->collection->setInterval(new \DateTime($from), new \DateTime($to));

        $actual = [];
        foreach ($this->collection->getReports() as $report) {
            $actual[] = [$report->getData('period'), $report->getData('start'), $report->getData('end')];
        }

        $this->assertSame($expected, $actual);
    }

    public static function monthIntervalsDataProvider(): array
    {
        return [
            'January 31' => ['2023-01-31', '2023-04-10', [
                ['01/2023', '2023-01-31 00:00:00', '2023-01-31 23:59:59'],
                ['02/2023', '2023-02-01 00:00:00', '2023-02-28 23:59:59'],
                ['03/2023', '2023-03-01 00:00:00', '2023-03-31 23:59:59'],
                ['04/2023', '2023-04-01 00:00:00', '2023-04-10 23:59:59'],
            ]],
            'January 30 non-leap year' => ['2023-01-30', '2023-03-10', [
                ['01/2023', '2023-01-30 00:00:00', '2023-01-31 23:59:59'],
                ['02/2023', '2023-02-01 00:00:00', '2023-02-28 23:59:59'],
                ['03/2023', '2023-03-01 00:00:00', '2023-03-10 23:59:59'],
            ]],
            'January 29 leap year' => ['2024-01-29', '2024-04-10', [
                ['01/2024', '2024-01-29 00:00:00', '2024-01-31 23:59:59'],
                ['02/2024', '2024-02-01 00:00:00', '2024-02-29 23:59:59'],
                ['03/2024', '2024-03-01 00:00:00', '2024-03-31 23:59:59'],
                ['04/2024', '2024-04-01 00:00:00', '2024-04-10 23:59:59'],
            ]],
            'August 31' => ['2023-08-31', '2023-11-10', [
                ['08/2023', '2023-08-31 00:00:00', '2023-08-31 23:59:59'],
                ['09/2023', '2023-09-01 00:00:00', '2023-09-30 23:59:59'],
                ['10/2023', '2023-10-01 00:00:00', '2023-10-31 23:59:59'],
                ['11/2023', '2023-11-01 00:00:00', '2023-11-10 23:59:59'],
            ]],
            'mid-month' => ['2023-01-15', '2023-03-10', [
                ['01/2023', '2023-01-15 00:00:00', '2023-01-31 23:59:59'],
                ['02/2023', '2023-02-01 00:00:00', '2023-02-28 23:59:59'],
                ['03/2023', '2023-03-01 00:00:00', '2023-03-10 23:59:59'],
            ]],
            'year boundary' => ['2023-12-31', '2024-03-10', [
                ['12/2023', '2023-12-31 00:00:00', '2023-12-31 23:59:59'],
                ['01/2024', '2024-01-01 00:00:00', '2024-01-31 23:59:59'],
                ['02/2024', '2024-02-01 00:00:00', '2024-02-29 23:59:59'],
                ['03/2024', '2024-03-01 00:00:00', '2024-03-10 23:59:59'],
            ]],
            'adjacent months' => ['2023-01-31', '2023-02-10', [
                ['01/2023', '2023-01-31 00:00:00', '2023-01-31 23:59:59'],
                ['02/2023', '2023-02-01 00:00:00', '2023-02-10 23:59:59'],
            ]],
            'same month' => ['2023-01-29', '2023-01-31', [
                ['01/2023', '2023-01-29 00:00:00', '2023-01-31 23:59:59'],
            ]],
        ];
    }

    /**
     * @return array
     */
    public static function intervalsDataProvider()
    {
        return [
            [
                'period' => 'day',
                'fromDate' => new \DateTime('-3 day'),
                'toDate' => new \DateTime('+3 day'),
                'size' => 7
            ],
            [
                'period' => 'month',
                'fromDate' => new \DateTime('2015-01-15 11:11:11'),
                'toDate' => new \DateTime('2015-01-25 11:11:11'),
                'size' => 1
            ],
            [
                'period' => 'month',
                'fromDate' => new \DateTime('2015-01-15 11:11:11'),
                'toDate' => new \DateTime('2015-02-25 11:11:11'),
                'size' => 2
            ],
            [
                'period' => 'year',
                'fromDate' => new \DateTime('2015-01-15 11:11:11'),
                'toDate' => new \DateTime('2015-01-25 11:11:11'),
                'size' => 1
            ],
            [
                'period' => 'year',
                'fromDate' => new \DateTime('2014-01-15 11:11:11'),
                'toDate' => new \DateTime('2015-01-25 11:11:11'),
                'size' => 2
            ],
            [
                'period' => null,
                'fromDate' => new \DateTime('-3 day'),
                'toDate' => new \DateTime('+3 day'),
                'size' => 0
            ]
        ];
    }

    /**
     * @param \DateTimeInterface $dateStart
     * @return string
     */
    public function formatDate(\DateTimeInterface $dateStart): string
    {
        $formatter = new \IntlDateFormatter(
            "en_US",
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::SHORT,
            new \DateTimeZone('America/Los_Angeles')
        );

        return $formatter->format($dateStart);
    }
}

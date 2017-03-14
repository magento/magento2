<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Report;

use Magento\Reports\Model\ResourceModel\Report\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Report\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timezoneMock;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Report\Collection\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMockBuilder('Magento\Framework\Data\Collection\EntityFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->timezoneMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->getMock();
        $this->factoryMock = $this->getMockBuilder('Magento\Reports\Model\ResourceModel\Report\Collection\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->timezoneMock
            ->expects($this->any())
            ->method('formatDateTime')
            ->will($this->returnCallback([$this, 'formatDateTime']));

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
        $this->assertEquals(null, $this->collection->getStoreIds());
        $this->collection->setStoreIds($storeIds);
        $this->assertEquals($storeIds, $this->collection->getStoreIds());
    }

    /**
     * @param string $period
     * @param \DateTime $fromDate
     * @param \DateTime $toDate
     * @param int $size
     * @dataProvider intervalsDataProvider
     * @return void
     */
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
        $this->assertEquals(null, $this->collection->getPageSize());
        $this->collection->setPageSize($pageSize);
        $this->assertEquals($pageSize, $this->collection->getPageSize());
    }

    /**
     * @param string $period
     * @param \DateTime $fromDate
     * @param \DateTime $toDate
     * @param int $size
     * @dataProvider intervalsDataProvider
     * @return void
     */
    public function testGetReports($period, $fromDate, $toDate, $size)
    {
        $this->collection->setPeriod($period);
        $this->collection->setInterval($fromDate, $toDate);
        $reports = $this->collection->getReports();
        foreach ($reports as $report) {
            $this->assertInstanceOf('\Magento\Framework\DataObject', $report);
            $reportData = $report->getData();
            $this->assertTrue(empty($reportData['children']));
            $this->assertTrue($reportData['is_empty']);
        }
        $this->assertEquals($size, count($reports));
    }

    /**
     * @return void
     */
    public function testLoadData()
    {
        $this->assertInstanceOf(
            '\Magento\Reports\Model\ResourceModel\Report\Collection',
            $this->collection->loadData()
        );
    }

    /**
     * @return array
     */
    public function intervalsDataProvider()
    {
        return [
            [
                '_period' => 'day',
                '_from' => new \DateTime('-3 day', new \DateTimeZone('UTC')),
                '_to' => new \DateTime('+3 day', new \DateTimeZone('UTC')),
                'size' => 7
            ],
            [
                '_period' => 'month',
                '_from' => new \DateTime('2015-01-15 11:11:11', new \DateTimeZone('UTC')),
                '_to' => new \DateTime('2015-01-25 11:11:11', new \DateTimeZone('UTC')),
                'size' => 1
            ],
            [
                '_period' => 'month',
                '_from' => new \DateTime('2015-01-15 11:11:11', new \DateTimeZone('UTC')),
                '_to' => new \DateTime('2015-02-25 11:11:11', new \DateTimeZone('UTC')),
                'size' => 2
            ],
            [
                '_period' => 'year',
                '_from' => new \DateTime('2015-01-15 11:11:11', new \DateTimeZone('UTC')),
                '_to' => new \DateTime('2015-01-25 11:11:11', new \DateTimeZone('UTC')),
                'size' => 1
            ],
            [
                '_period' => 'year',
                '_from' => new \DateTime('2014-01-15 11:11:11', new \DateTimeZone('UTC')),
                '_to' => new \DateTime('2015-01-25 11:11:11', new \DateTimeZone('UTC')),
                'size' => 2
            ],
            [
                '_period' => null,
                '_from' => new \DateTime('-3 day', new \DateTimeZone('UTC')),
                '_to' => new \DateTime('+3 day', new \DateTimeZone('UTC')),
                'size' => 0
            ]
        ];
    }

    /**
     * Format datetime.
     *
     * @return string
     */
    public function formatDateTime()
    {
        $args = func_get_args();

        $dateStart = $args[0];

        $formatter = new \IntlDateFormatter(
            "en_US",
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::SHORT,
            new \DateTimeZone('America/Los_Angeles')
        );

        return $formatter->format($dateStart);
    }
}

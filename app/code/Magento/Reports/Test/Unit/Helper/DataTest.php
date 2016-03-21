<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Helper;

use Magento\Reports\Helper\Data;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Helper\Data
     */
    protected $data;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Reports\Model\ItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemFactoryMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder('Magento\Framework\App\Helper\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemFactoryMock = $this->getMockBuilder('Magento\Reports\Model\ItemFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->data = new Data(
            $this->contextMock,
            $this->itemFactoryMock
        );
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $period
     * @param array $results
     * @dataProvider intervalsDataProvider
     * @return void
     */
    public function testGetIntervals($from, $to, $period, $results)
    {
        $this->assertEquals($this->data->getIntervals($from, $to, $period), $results);
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $period
     * @param array $results
     * @dataProvider intervalsDataProvider
     * @return void
     */
    public function testPrepareIntervalsCollection($from, $to, $period, $results)
    {
        $collection = $this->getMockBuilder('Magento\Framework\Data\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['addItem'])
            ->getMock();

        $item = $this->getMockBuilder('Magento\Reports\Model\Item')
            ->disableOriginalConstructor()
            ->setMethods(['setPeriod', 'setIsEmpty'])
            ->getMock();

        $this->itemFactoryMock->expects($this->exactly(count($results)))
            ->method('create')
            ->willReturn($item);
        $item->expects($this->exactly(count($results)))
            ->method('setIsEmpty');
        $collection->expects($this->exactly(count($results)))
            ->method('addItem');

        foreach ($results as $key => $result) {
            $item->expects($this->at($key + $key))
                ->method('setPeriod')
                ->with($result);
        }

        $this->data->prepareIntervalsCollection($collection, $from, $to, $period);
    }

    /**
     * @return array
     */
    public function intervalsDataProvider()
    {
        return [
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2000-01-15 11:00:00',
                'period' => \Magento\Reports\Helper\Data::REPORT_PERIOD_TYPE_DAY,
                'results' => ['2000-01-15']
            ],
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2000-01-17 10:00:00',
                'period' => \Magento\Reports\Helper\Data::REPORT_PERIOD_TYPE_MONTH,
                'results' => ['2000-01']
            ],
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2000-02-15 10:00:00',
                'period' => \Magento\Reports\Helper\Data::REPORT_PERIOD_TYPE_YEAR,
                'results' => ['2000']
            ],
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2000-01-16 11:00:00',
                'period' => \Magento\Reports\Helper\Data::REPORT_PERIOD_TYPE_DAY,
                'results' => ['2000-01-15', '2000-01-16']
            ],
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2000-02-17 10:00:00',
                'period' => \Magento\Reports\Helper\Data::REPORT_PERIOD_TYPE_MONTH,
                'results' => ['2000-01', '2000-02']
            ],
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2003-02-15 10:00:00',
                'period' => \Magento\Reports\Helper\Data::REPORT_PERIOD_TYPE_YEAR,
                'results' => ['2000', '2001', '2002', '2003']
            ],
            [
                'from' => '',
                'to' => '',
                'period' => \Magento\Reports\Helper\Data::REPORT_PERIOD_TYPE_YEAR,
                'results' => []
            ]
        ];
    }
}

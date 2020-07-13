<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Collection;
use Magento\Reports\Helper\Data;
use Magento\Reports\Model\Item;
use Magento\Reports\Model\ItemFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Reports\Helper\Data class.
 */
class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var ItemFactory|MockObject
     */
    protected $itemFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemFactoryMock = $this->getMockBuilder(ItemFactory::class)
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
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addItem'])
            ->getMock();

        $item = $this->getMockBuilder(Item::class)
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
                'period' => Data::REPORT_PERIOD_TYPE_DAY,
                'results' => ['2000-01-15'],
            ],
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2000-01-17 10:00:00',
                'period' => Data::REPORT_PERIOD_TYPE_MONTH,
                'results' => ['2000-01'],
            ],
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2000-02-15 10:00:00',
                'period' => Data::REPORT_PERIOD_TYPE_YEAR,
                'results' => ['2000']
            ],
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2000-01-16 11:00:00',
                'period' => Data::REPORT_PERIOD_TYPE_DAY,
                'results' => ['2000-01-15', '2000-01-16'],
            ],
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2000-02-17 10:00:00',
                'period' => Data::REPORT_PERIOD_TYPE_MONTH,
                'results' => ['2000-01', '2000-02'],
            ],
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2003-02-15 10:00:00',
                'period' => Data::REPORT_PERIOD_TYPE_YEAR,
                'results' => ['2000', '2001', '2002', '2003'],
            ],
            [
                'from' => '2000-12-31 10:00:00',
                'to' => '2001-01-01 10:00:00',
                'period' => Data::REPORT_PERIOD_TYPE_YEAR,
                'results' => ['2000', '2001'],
            ],
            [
                'from' => '',
                'to' => '',
                'period' => Data::REPORT_PERIOD_TYPE_YEAR,
                'results' => [],
            ]
        ];
    }
}

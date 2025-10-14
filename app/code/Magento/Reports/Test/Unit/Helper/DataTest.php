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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemFactoryMock = $this->getMockBuilder(ItemFactory::class)
            ->onlyMethods(['create'])
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
     *
     * @return void
     * @dataProvider intervalsDataProvider
     */
    public function testGetIntervals($from, $to, $period, $results): void
    {
        $this->assertEquals($this->data->getIntervals($from, $to, $period), $results);
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $period
     * @param array $results
     *
     * @return void
     * @dataProvider intervalsDataProvider
     */
    public function testPrepareIntervalsCollection($from, $to, $period, $results): void
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addItem'])
            ->getMock();

        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setIsEmpty'])
            ->addMethods(['setPeriod'])
            ->getMock();

        $this->itemFactoryMock->expects($this->exactly(count($results)))
            ->method('create')
            ->willReturn($item);
        $item->expects($this->exactly(count($results)))
            ->method('setIsEmpty');
        $collection->expects($this->exactly(count($results)))
            ->method('addItem');

        $withArgs = [];

        foreach ($results as $result) {
            $withArgs[] = [$result];
        }
        $item
            ->method('setPeriod')
            ->willReturnCallback(function (...$withArgs) {
                return null;
            });

        $this->data->prepareIntervalsCollection($collection, $from, $to, $period);
    }

    /**
     * @return array
     */
    public static function intervalsDataProvider(): array
    {
        return [
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2000-01-15 11:00:00',
                'period' => Data::REPORT_PERIOD_TYPE_DAY,
                'results' => ['2000-01-15']
            ],
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2000-01-17 10:00:00',
                'period' => Data::REPORT_PERIOD_TYPE_MONTH,
                'results' => ['2000-01']
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
                'results' => ['2000-01-15', '2000-01-16']
            ],
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2000-02-17 10:00:00',
                'period' => Data::REPORT_PERIOD_TYPE_MONTH,
                'results' => ['2000-01', '2000-02']
            ],
            [
                'from' => '2000-01-15 10:00:00',
                'to' => '2003-02-15 10:00:00',
                'period' => Data::REPORT_PERIOD_TYPE_YEAR,
                'results' => ['2000', '2001', '2002', '2003']
            ],
            [
                'from' => '2000-12-31 10:00:00',
                'to' => '2001-01-01 10:00:00',
                'period' => Data::REPORT_PERIOD_TYPE_YEAR,
                'results' => ['2000', '2001']
            ],
            [
                'from' => '',
                'to' => '',
                'period' => Data::REPORT_PERIOD_TYPE_YEAR,
                'results' => []
            ]
        ];
    }
}

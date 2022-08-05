<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Dashboard;

use Magento\Backend\Helper\Dashboard\Order as OrderHelper;
use Magento\Backend\Model\Dashboard\Chart;
use Magento\Backend\Model\Dashboard\Chart\Date as DateRetriever;
use Magento\Backend\Model\Dashboard\Period;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Model\ResourceModel\Order\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChartTest extends TestCase
{
    /**
     * @var Chart
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var DateRetriever|MockObject
     */
    private $dateRetrieverMock;

    /**
     * @var OrderHelper|MockObject
     */
    private $orderHelperMock;

    /**
     * @var Collection|MockObject
     */
    private $collectionMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $this->dateRetrieverMock = $this->getMockBuilder(DateRetriever::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderHelperMock = $this->getMockBuilder(OrderHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderHelperMock->method('getCollection')
            ->willReturn($this->collectionMock);

        $period = $this->objectManagerHelper->getObject(Period::class);

        $this->model = $this->objectManagerHelper->getObject(
            Chart::class,
            [
                'dateRetriever' => $this->dateRetrieverMock,
                'orderHelper' => $this->orderHelperMock,
                'period' => $period
            ]
        );
    }

    /**
     * @param string $period
     * @param string $chartParam
     * @param array $result
     *
     * @return void
     * @dataProvider getByPeriodDataProvider
     */
    public function testGetByPeriod(string $period, string $chartParam, array $result): void
    {
        $this->orderHelperMock
            ->method('setParam')
            ->withConsecutive(
                ['store', null],
                ['website', null],
                ['group', null],
                ['period', $period]
            );

        $this->dateRetrieverMock->expects($this->once())
            ->method('getByPeriod')
            ->with($period)
            ->willReturn(array_map(static function ($item) {
                return $item['x'];
            }, $result));

        $this->collectionMock->method('count')
            ->willReturn(2);

        $valueMap = [];
        foreach ($result as $resultItem) {
            $dataObjectMock = $this->getMockBuilder(DataObject::class)
                ->disableOriginalConstructor()
                ->getMock();
            $dataObjectMock->method('getData')
                ->with($chartParam)
                ->willReturn($resultItem['y']);

            $valueMap[] = [
                'range',
                $resultItem['x'],
                $dataObjectMock
            ];
        }
        $this->collectionMock->method('getItemByColumnValue')
            ->willReturnMap($valueMap);

        $this->assertEquals(
            $result,
            $this->model->getByPeriod($period, $chartParam)
        );
    }

    /**
     * @return array
     */
    public function getByPeriodDataProvider(): array
    {
        return [
            [
                Period::PERIOD_7_DAYS,
                'revenue',
                [
                    [
                        'x' => '2020-01-21',
                        'y' => 0
                    ],
                    [
                        'x' => '2020-01-22',
                        'y' => 2
                    ],
                    [
                        'x' => '2020-01-23',
                        'y' => 0
                    ],
                    [
                        'x' => '2020-01-24',
                        'y' => 7
                    ]
                ]
            ],
            [
                Period::PERIOD_1_MONTH,
                'quantity',
                [
                    [
                        'x' => '2020-01-21',
                        'y' => 0
                    ],
                    [
                        'x' => '2020-01-22',
                        'y' => 2
                    ],
                    [
                        'x' => '2020-01-23',
                        'y' => 0
                    ],
                    [
                        'x' => '2020-01-24',
                        'y' => 7
                    ]
                ]
            ],
            [
                Period::PERIOD_1_YEAR,
                'quantity',
                [
                    [
                        'x' => '2020-01',
                        'y' => 0
                    ],
                    [
                        'x' => '2020-02',
                        'y' => 2
                    ],
                    [
                        'x' => '2020-03',
                        'y' => 0
                    ],
                    [
                        'x' => '2020-04',
                        'y' => 7
                    ]
                ]
            ]
        ];
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Dashboard;

use Magento\Backend\Helper\Dashboard\Data as DataHelper;
use Magento\Backend\Helper\Dashboard\Order as OrderHelper;
use Magento\Backend\Model\Dashboard\Chart;
use Magento\Backend\Model\Dashboard\Chart\Date as DateRetriever;
use Magento\Framework\App\RequestInterface;
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
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var DateRetriever|MockObject
     */
    private $dateRetrieverMock;

    /**
     * @var DataHelper|MockObject
     */
    private $dataHelperMock;

    /**
     * @var OrderHelper|MockObject
     */
    private $orderHelperMock;

    /**
     * @var Collection|MockObject
     */
    private $collectionMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->requestMock->method('getParam')->willReturn(null);

        $this->dateRetrieverMock = $this->getMockBuilder(DateRetriever::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataHelperMock = $this->getMockBuilder(DataHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataHelperMock->method('getDatePeriods')
            ->willReturn([
                '24h' => __('Last 24 Hours'),
                '7d' => __('Last 7 Days'),
                '1m' => __('Current Month'),
                '1y' => __('YTD'),
                '2y' => __('2YTD')
            ]);

        $this->orderHelperMock = $this->getMockBuilder(OrderHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderHelperMock->method('getCollection')
            ->willReturn($this->collectionMock);

        $this->model = $this->objectManagerHelper->getObject(
            Chart::class,
            [
                'request' => $this->requestMock,
                'dateRetriever' => $this->dateRetrieverMock,
                'dataHelper' => $this->dataHelperMock,
                'orderHelper' => $this->orderHelperMock
            ]
        );
    }

    /**
     * @param string $period
     * @param string $chartParam
     * @param array $result
     * @dataProvider getByPeriodDataProvider
     */
    public function testGetByPeriod($period, $chartParam, $result)
    {
        $this->orderHelperMock->expects($this->at(0))
            ->method('setParam')
            ->with('store', null);
        $this->orderHelperMock->expects($this->at(1))
            ->method('setParam')
            ->with('website', null);
        $this->orderHelperMock->expects($this->at(2))
            ->method('setParam')
            ->with('group', null);
        $this->orderHelperMock->expects($this->at(3))
            ->method('setParam')
            ->with('period', $period);

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

    public function getByPeriodDataProvider(): array
    {
        return [
            [
                '7d',
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
                '1m',
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
                '1y',
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

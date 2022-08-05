<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer\Category;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Category\AvailabilityFlag;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\State;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AvailabilityFlagTest extends TestCase
{
    /**
     * @var array
     */
    protected $filters;

    /**
     * @var MockObject
     */
    protected $filterMock;

    /**
     * @var MockObject
     */
    protected $layerMock;

    /**
     * @var MockObject
     */
    protected $stateMock;

    /**
     * @var AvailabilityFlag
     */
    protected $model;

    protected function setUp(): void
    {
        $this->filterMock = $this->createMock(AbstractFilter::class);
        $this->filters = [$this->filterMock];
        $this->layerMock = $this->createMock(Layer::class);
        $this->stateMock = $this->createMock(State::class);
        $this->model = new AvailabilityFlag();
    }

    /**
     * @param int $itemsCount
     * @param array $filters
     * @param bool $expectedResult
     *
     * @dataProvider isEnabledDataProvider
     * @covers \Magento\Catalog\Model\Layer\Category\AvailabilityFlag::isEnabled
     * @covers \Magento\Catalog\Model\Layer\Category\AvailabilityFlag::canShowOptions
     */
    public function testIsEnabled($itemsCount, $filters, $expectedResult)
    {
        $this->layerMock->expects($this->any())->method('getState')->willReturn($this->stateMock);
        $this->stateMock->expects($this->any())->method('getFilters')->willReturn($filters);
        $this->filterMock->expects($this->once())->method('getItemsCount')->willReturn($itemsCount);

        $this->assertEquals($expectedResult, $this->model->isEnabled($this->layerMock, $this->filters));
    }

    /**
     * @return array
     */
    public function isEnabledDataProvider()
    {
        return [
            [
                'itemsCount' => 0,
                'filters' => [],
                'expectedResult' => false,
            ],
            [
                'itemsCount' => 0,
                'filters' => ['filter'],
                'expectedResult' => true,
            ],
            [
                'itemsCount' => 1,
                'filters' => 0,
                'expectedResult' => true,
            ],
            [
                'itemsCount' => 1,
                'filters' => ['filter'],
                'expectedResult' => true,
            ]
        ];
    }
}

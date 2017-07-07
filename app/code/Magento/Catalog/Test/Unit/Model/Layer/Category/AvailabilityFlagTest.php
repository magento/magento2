<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Layer\Category;

use \Magento\Catalog\Model\Layer\Category\AvailabilityFlag;

class AvailabilityFlagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $filters;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateMock;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\AvailabilityFlag
     */
    protected $model;

    protected function setUp()
    {
        $this->filterMock = $this->getMock(
            \Magento\Catalog\Model\Layer\Filter\AbstractFilter::class, [], [], '', false
        );
        $this->filters = [$this->filterMock];
        $this->layerMock = $this->getMock(\Magento\Catalog\Model\Layer::class, [], [], '', false);
        $this->stateMock = $this->getMock(\Magento\Catalog\Model\Layer\State::class, [], [], '', false);
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
        $this->layerMock->expects($this->any())->method('getState')->will($this->returnValue($this->stateMock));
        $this->stateMock->expects($this->any())->method('getFilters')->will($this->returnValue($filters));
        $this->filterMock->expects($this->once())->method('getItemsCount')->will($this->returnValue($itemsCount));

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

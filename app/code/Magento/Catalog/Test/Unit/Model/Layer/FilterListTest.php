<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer;

use \Magento\Catalog\Model\Layer\FilterList;

class FilterListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $attributeListMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $attributeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $layerMock;

    /**
     * @var \Magento\Catalog\Model\Layer\FilterList
     */
    protected $model;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->attributeListMock = $this->createMock(
            \Magento\Catalog\Model\Layer\Category\FilterableAttributeList::class
        );
        $this->attributeMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $filters = [
            FilterList::CATEGORY_FILTER => 'CategoryFilterClass',
            FilterList::PRICE_FILTER => 'PriceFilterClass',
            FilterList::DECIMAL_FILTER => 'DecimalFilterClass',
            FilterList::ATTRIBUTE_FILTER => 'AttributeFilterClass',

        ];
        $this->layerMock = $this->createMock(\Magento\Catalog\Model\Layer::class);

        $this->model = new FilterList($this->objectManagerMock, $this->attributeListMock, $filters);
    }

    /**
     * @param string $method
     * @param string $value
     * @param string $expectedClass
     * @dataProvider getFiltersDataProvider
     *
     * @covers \Magento\Catalog\Model\Layer\FilterList::getFilters
     * @covers \Magento\Catalog\Model\Layer\FilterList::createAttributeFilter
     * @covers \Magento\Catalog\Model\Layer\FilterList::__construct
     */
    public function testGetFilters($method, $value, $expectedClass)
    {
        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->willReturn('filter');

        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with(
                $expectedClass,
                [
                    'data' => ['attribute_model' => $this->attributeMock],
                    'layer' => $this->layerMock
                ]
            )
            ->willReturn('filter');

        $this->attributeMock->expects($this->once())
            ->method($method)
            ->willReturn($value);

        $this->attributeListMock->expects($this->once())
            ->method('getList')
            ->willReturn([$this->attributeMock]);

        $this->assertEquals(['filter', 'filter'], $this->model->getFilters($this->layerMock));
    }

    /**
     * @return array
     */
    public function getFiltersDataProvider()
    {
        return [
            [
                'method' => 'getAttributeCode',
                'value' => FilterList::PRICE_FILTER,
                'expectedClass' => 'PriceFilterClass',
            ],
            [
                'method' => 'getBackendType',
                'value' => FilterList::DECIMAL_FILTER,
                'expectedClass' => 'DecimalFilterClass',
            ],
            [
                'method' => 'getAttributeCode',
                'value' => null,
                'expectedClass' => 'AttributeFilterClass',
            ]
        ];
    }
}

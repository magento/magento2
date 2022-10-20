<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer;

use Magento\Catalog\Model\Config\LayerCategoryConfig;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Category\FilterableAttributeList;
use Magento\Catalog\Model\Layer\FilterList;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Check whenever the given filters list matches the expected result
 */
class FilterListTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    /**
     * @var MockObject
     */
    protected $attributeListMock;

    /**
     * @var MockObject
     */
    protected $attributeMock;

    /**
     * @var MockObject
     */
    protected $layerMock;

    /**
     * @var FilterList
     */
    protected $model;

    /**
     * @var LayerCategoryConfig|MockObject
     */
    private $layerCategoryConfigMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->attributeListMock = $this->createMock(
            FilterableAttributeList::class
        );
        $this->attributeMock = $this->createMock(Attribute::class);
        $filters = [
            FilterList::CATEGORY_FILTER => 'CategoryFilterClass',
            FilterList::PRICE_FILTER => 'PriceFilterClass',
            FilterList::DECIMAL_FILTER => 'DecimalFilterClass',
            FilterList::ATTRIBUTE_FILTER => 'AttributeFilterClass'

        ];
        $this->layerMock = $this->createMock(Layer::class);
        $this->layerCategoryConfigMock = $this->createMock(LayerCategoryConfig::class);

        $this->model = new FilterList(
            $this->objectManagerMock,
            $this->attributeListMock,
            $this->layerCategoryConfigMock,
            $filters
        );
    }

    /**
     * @param string $method
     * @param string|null $value
     * @param string $expectedClass
     *
     * @return void
     * @dataProvider getFiltersDataProvider
     * @covers \Magento\Catalog\Model\Layer\FilterList::getFilters
     * @covers \Magento\Catalog\Model\Layer\FilterList::createAttributeFilter
     * @covers \Magento\Catalog\Model\Layer\FilterList::__construct
     */
    public function testGetFilters(string $method, ?string $value, string $expectedClass): void
    {
        $this->objectManagerMock
            ->method('create')
            ->withConsecutive(
                [],
                [
                    $expectedClass,
                    [
                        'data' => ['attribute_model' => $this->attributeMock],
                        'layer' => $this->layerMock
                    ]
                ]
            )
            ->willReturnOnConsecutiveCalls('filter', 'filter');

        $this->attributeMock->expects($this->once())
            ->method($method)
            ->willReturn($value);

        $this->attributeListMock->expects($this->once())
            ->method('getList')
            ->willReturn([$this->attributeMock]);

        $this->layerCategoryConfigMock->expects($this->once())
            ->method('isCategoryFilterVisibleInLayerNavigation')
            ->willReturn(true);

        $this->assertEquals(['filter', 'filter'], $this->model->getFilters($this->layerMock));
    }

    /**
     * Test filters list result when category should not be included.
     *
     * @param string $method
     * @param string $value
     * @param string $expectedClass
     * @param array $expectedResult
     *
     * @return void
     * @dataProvider getFiltersWithoutCategoryDataProvider
     */
    public function testGetFiltersWithoutCategoryFilter(
        string $method,
        string $value,
        string $expectedClass,
        array $expectedResult
    ): void {
        $this->objectManagerMock
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

        $this->layerCategoryConfigMock->expects($this->once())
            ->method('isCategoryFilterVisibleInLayerNavigation')
            ->willReturn(false);

        $this->assertEquals($expectedResult, $this->model->getFilters($this->layerMock));
    }

    /**
     * @return array
     */
    public function getFiltersDataProvider(): array
    {
        return [
            [
                'method' => 'getAttributeCode',
                'value' => FilterList::PRICE_FILTER,
                'expectedClass' => 'PriceFilterClass'
            ],
            [
                'method' => 'getBackendType',
                'value' => FilterList::DECIMAL_FILTER,
                'expectedClass' => 'DecimalFilterClass'
            ],
            [
                'method' => 'getAttributeCode',
                'value' => null,
                'expectedClass' => 'AttributeFilterClass'
            ]
        ];
    }

    /**
     * Provides attribute filters without category item.
     *
     * @return array
     */
    public function getFiltersWithoutCategoryDataProvider(): array
    {
        return [
            'Filters contains only price attribute' => [
                'method' => 'getAttributeCode',
                'value' => FilterList::PRICE_FILTER,
                'expectedClass' => 'PriceFilterClass',
                'expectedResult' => [
                    'filter'
                ]
            ]
        ];
    }
}

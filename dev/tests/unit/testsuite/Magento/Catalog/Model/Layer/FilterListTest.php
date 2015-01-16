<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Layer;

class FilterListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layerMock;

    /**
     * @var \Magento\Catalog\Model\Layer\FilterList
     */
    protected $model;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('\Magento\Framework\ObjectManagerInterface');
        $this->attributeListMock = $this->getMock(
            'Magento\Catalog\Model\Layer\Category\FilterableAttributeList', [], [], '', false
        );
        $this->attributeMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Eav\Attribute', [], [], '', false
        );
        $filters = [
            FilterList::CATEGORY_FILTER => 'CategoryFilterClass',
            FilterList::PRICE_FILTER => 'PriceFilterClass',
            FilterList::DECIMAL_FILTER => 'DecimalFilterClass',
            FilterList::ATTRIBUTE_FILTER => 'AttributeFilterClass',

        ];
        $this->layerMock = $this->getMock('\Magento\Catalog\Model\Layer', [], [], '', false);

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
            ->will($this->returnValue('filter'));

        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with($expectedClass, [
                'data' => ['attribute_model' => $this->attributeMock],
                'layer' => $this->layerMock])
            ->will($this->returnValue('filter'));

        $this->attributeMock->expects($this->once())
            ->method($method)
            ->will($this->returnValue($value));

        $this->attributeListMock->expects($this->once())
            ->method('getList')
            ->will($this->returnValue([$this->attributeMock]));

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

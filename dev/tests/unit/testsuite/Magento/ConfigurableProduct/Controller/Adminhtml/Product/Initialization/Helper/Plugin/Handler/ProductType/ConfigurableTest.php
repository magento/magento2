<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Handler\ProductType;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configurable
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeMock;

    protected function setUp()
    {
        $this->productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [
                'getConfigurableAttributesData',
                'getTypeInstance',
                'setConfigurableAttributesData',
                '__wakeup',
                'getTypeId'
            ],
            [],
            '',
            false
        );
        $this->productTypeMock = $this->getMock(
            '\Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            [],
            [],
            '',
            false
        );
        $this->productMock->expects(
            $this->any()
        )->method(
            'getTypeInstance'
        )->will(
            $this->returnValue($this->productTypeMock)
        );
        $this->model = new Configurable();
    }

    public function testHandleWithNonConfigurableProductType()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue('some product type'));
        $this->productMock->expects($this->never())->method('getConfigurableAttributesData');
        $this->model->handle($this->productMock);
    }

    public function testHandleWithoutOriginalProductAttributes()
    {
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );
        $this->productTypeMock->expects(
            $this->once()
        )->method(
            'getConfigurableAttributesAsArray'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue([])
        );

        $attributeData = [
            [
                'attribute_id' => 1,
                'values' => [['value_index' => 0, 'pricing_value' => 10, 'is_percent' => 1]],
            ],
        ];
        $this->productMock->expects(
            $this->once()
        )->method(
            'getConfigurableAttributesData'
        )->will(
            $this->returnValue($attributeData)
        );

        $expected = [
            [
                'attribute_id' => 1,
                'values' => [['value_index' => 0, 'pricing_value' => 0, 'is_percent' => 0]],
            ],
        ];

        $this->productMock->expects($this->once())->method('setConfigurableAttributesData')->with($expected);
        $this->model->handle($this->productMock);
    }

    public function testHandleWithOriginalProductAttributes()
    {
        $originalAttributes = [
            ['id' => 1, 'values' => [['value_index' => 0, 'is_percent' => 10, 'pricing_value' => 50]]],
        ];

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );
        $this->productTypeMock->expects(
            $this->once()
        )->method(
            'getConfigurableAttributesAsArray'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue($originalAttributes)
        );

        $attributeData = [
            [
                'attribute_id' => 1,
                'values' => [
                    ['value_index' => 0, 'pricing_value' => 10, 'is_percent' => 1],
                    ['value_index' => 1, 'pricing_value' => 100, 'is_percent' => 200],
                ],
            ],
        ];
        $this->productMock->expects(
            $this->once()
        )->method(
            'getConfigurableAttributesData'
        )->will(
            $this->returnValue($attributeData)
        );

        $expected = [
            [
                'attribute_id' => 1,
                'values' => [
                    ['value_index' => 0, 'pricing_value' => 50, 'is_percent' => 10],
                    ['value_index' => 1, 'pricing_value' => 0, 'is_percent' => 0],
                ],
            ],
        ];

        $this->productMock->expects($this->once())->method('setConfigurableAttributesData')->with($expected);
        $this->model->handle($this->productMock);
    }
}

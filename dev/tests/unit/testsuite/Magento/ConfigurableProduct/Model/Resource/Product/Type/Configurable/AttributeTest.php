<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    public function setUp()
    {
        $this->resource = $this->getMockBuilder('Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogData = $this->getMockBuilder('Magento\Catalog\Helper\Data')
            ->setMethods(['isPriceGlobal'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute',
            [
                'resource' => $this->resource,
                'storeManager' => $this->storeManager,
                'catalogData' => $this->catalogData,
            ]
        );
    }

    public function testSavePrices()
    {
        $rowSet = [
            0 => [
                    'value_id' => '1',
                    'product_super_attribute_id' => '1',
                    'value_index' => '12',
                    'is_percent' => '0',
                    'pricing_value' => '3.0000',
                    'website_id' => '0',
                ],
            1 => [
                    'value_id' => '2',
                    'product_super_attribute_id' => '1',
                    'value_index' => '13',
                    'is_percent' => '0',
                    'pricing_value' => '8.0000',
                    'website_id' => '0',
                ],
        ];

        $values = [
            12 => [
                    'value_index' => '12',
                    'pricing_value' => '',
                    'is_percent' => '0',
                    'include' => '1',
                ],
            13 => [
                    'value_index' => '13',
                    'pricing_value' => '5',
                    'is_percent' => '0',
                    'include' => '1',
                ],
            14 => [
                    'value_index' => '14',
                    'pricing_value' => '3',
                    'is_percent' => '0',
                    'include' => '1',
                ],
        ];

        $attribute = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute')
            ->setMethods(['getValues', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resource->expects($this->once())->method('getConnection')->with('core_write')->willReturn($adapterMock);
        $this->catalogData->expects($this->any())->method('isPriceGlobal')->willReturn(1);
        $attribute->expects($this->once())->method('getValues')->willReturn($values);
        $adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $selectMock->expects($this->once())->method('from')->with(null)->will($this->returnSelf());
        $selectMock->expects($this->at(1))
            ->method('where')
            ->with('product_super_attribute_id = :product_super_attribute_id')
            ->will($this->returnSelf());
        $selectMock->expects($this->at(2))
            ->method('where')
            ->with('website_id = :website_id')
            ->will($this->returnSelf());
        $attribute->expects($this->any())->method('getId')->willReturn(1);
        $adapterMock->expects($this->once())
            ->method('fetchAll')
            ->with($selectMock, ['product_super_attribute_id' => 1, 'website_id' => 0])
            ->willReturn($rowSet);

        $this->assertEquals($this->model, $this->model->savePrices($attribute));
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Rule\Condition;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogRule\Model\Rule\Condition\Product */
    protected $product;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $productModel;

    /** @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $productResource;

    /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $eavAttributeResource;

    protected function setUp()
    {
        $this->config = $this->getMock('Magento\Eav\Model\Config', ['getAttribute'], [], '', false);
        $this->productModel = $this->getMock(
            'Magento\Catalog\Model\Product',
            [
                '__wakeup',
                'getAvailableInCategories',
                'hasData',
                'getData',
                'getId',
                'getStoreId',
                'getResource',
                'addAttributeToSelect',
            ],
            [],
            '',
            false
        );
        $this->productResource = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product',
            ['loadAllAttributes',
                'getAttributesByCode',
                'getAttribute'
            ],
            [],
            '',
            false
        );
        $this->eavAttributeResource = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Eav\Attribute',
            [
                '__wakeup',
                'isAllowedForRuleCondition',
                'getDataUsingMethod',
                'getAttributeCode',
                'getFrontendLabel',
                'isScopeGlobal',
                'getBackendType',
                'getFrontendInput'
            ],
            [],
            '',
            false
        );

        $this->productResource->expects($this->any())->method('loadAllAttributes')
            ->will($this->returnSelf());
        $this->productResource->expects($this->any())->method('getAttributesByCode')
            ->will($this->returnValue([$this->eavAttributeResource]));
        $this->eavAttributeResource->expects($this->any())->method('isAllowedForRuleCondition')
            ->will($this->returnValue(false));
        $this->eavAttributeResource->expects($this->any())->method('getAttributesByCode')
            ->will($this->returnValue(false));
        $this->eavAttributeResource->expects($this->any())->method('getAttributeCode')
            ->will($this->returnValue('1'));
        $this->eavAttributeResource->expects($this->any())->method('getFrontendLabel')
            ->will($this->returnValue('attribute_label'));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->product = $this->objectManagerHelper->getObject(
            'Magento\CatalogRule\Model\Rule\Condition\Product',
            [
                'config' => $this->config,
                'product' => $this->productModel,
                'productResource' => $this->productResource
            ]
        );
    }

    /**
     * @return void
     */
    public function testValidateMeetsCategory()
    {
        $this->product->setData('attribute', 'category_ids');
        $this->product->setData('value_parsed', '1');
        $this->product->setData('operator', '>=');

        $this->productModel->expects($this->once())->method('getAvailableInCategories')
            ->will($this->returnValue('2'));
        $this->assertTrue($this->product->validate($this->productModel));
    }

    /**
     * @dataProvider validateDataProvider
     *
     * @param string $attributeValue
     * @param string|array $parsedValue
     * @param string $newValue
     * @param string $operator
     * @param array $input
     * @return void
     */
    public function testValidateWithDatetimeValue($attributeValue, $parsedValue, $newValue, $operator, $input)
    {
        $this->product->setData('attribute', 'attribute_key');
        $this->product->setData('value_parsed', $parsedValue);
        $this->product->setData('operator', $operator);

        $this->config->expects($this->any())->method('getAttribute')
            ->will($this->returnValue($this->eavAttributeResource));

        $this->eavAttributeResource->expects($this->any())->method('isScopeGlobal')
            ->will($this->returnValue(false));
        $this->eavAttributeResource->expects($this->any())->method($input['method'])
            ->will($this->returnValue($input['type']));

        $this->productModel->expects($this->any())->method('hasData')
            ->will($this->returnValue(true));
        $this->productModel->expects($this->at(0))->method('getData')
            ->will($this->returnValue(['1' => ['1' => $attributeValue]]));
        $this->productModel->expects($this->any())->method('getData')
            ->will($this->returnValue($newValue));
        $this->productModel->expects($this->any())->method('getId')
            ->will($this->returnValue('1'));
        $this->productModel->expects($this->once())->method('getStoreId')
            ->will($this->returnValue('1'));
        $this->productModel->expects($this->any())->method('getResource')
            ->will($this->returnValue($this->productResource));

        $this->productResource->expects($this->any())->method('getAttribute')
            ->will($this->returnValue($this->eavAttributeResource));

        $this->product->collectValidatedAttributes($this->productModel);
        $this->assertTrue($this->product->validate($this->productModel));
    }

    /**
     * @return void
     */
    public function testValidateWithNoValue()
    {
        $this->product->setData('attribute', 'color');
        $this->product->setData('value_parsed', '1');
        $this->product->setData('operator', '!=');

        $this->productModel->expects($this->once())
            ->method('getData')
            ->with('color')
            ->willReturn(null);
        $this->assertFalse($this->product->validate($this->productModel));
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            [
                'attribute_value' => '12:12',
                'parsed_value' => '12:12',
                'new_value' => '12:13',
                'operator' => '>=',
                'input' => ['method' => 'getBackendType', 'type' => 'input_type'],
            ],
            [
                'attribute_value' => '1',
                'parsed_value' => '1',
                'new_value' => '2',
                'operator' => '>=',
                'input' => ['method' => 'getBackendType', 'type' => 'input_type']
            ],
            [
                'attribute_value' => '1',
                'parsed_value' => ['1' => '0'],
                'new_value' => ['1' => '1'],
                'operator' => '!()',
                'input' => ['method' => 'getFrontendInput', 'type' => 'multiselect']
            ]
        ];
    }
}

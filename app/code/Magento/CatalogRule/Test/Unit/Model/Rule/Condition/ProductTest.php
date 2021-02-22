<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Rule\Condition;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ProductTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\CatalogRule\Model\Rule\Condition\Product */
    protected $product;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Eav\Model\Config|\PHPUnit\Framework\MockObject\MockObject */
    protected $config;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject */
    protected $productModel;

    /** @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit\Framework\MockObject\MockObject */
    protected $productResource;

    /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit\Framework\MockObject\MockObject */
    protected $eavAttributeResource;

    /** @var \Magento\Catalog\Model\ProductCategoryList|\PHPUnit\Framework\MockObject\MockObject */
    private $productCategoryList;

    protected function setUp(): void
    {
        $this->config = $this->createPartialMock(\Magento\Eav\Model\Config::class, ['getAttribute']);
        $this->productModel = $this->createPartialMock(\Magento\Catalog\Model\Product::class, [
                '__wakeup',
                'hasData',
                'getData',
                'getId',
                'getStoreId',
                'getResource',
                'addAttributeToSelect',
                'getAttributesByCode'
            ]);

        $this->productCategoryList = $this->getMockBuilder(\Magento\Catalog\Model\ProductCategoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productResource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product::class)
            ->setMethods(['loadAllAttributes', 'getAttributesByCode', 'getAttribute', 'getConnection', 'getTable'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavAttributeResource = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            [
                '__wakeup',
                'isAllowedForRuleCondition',
                'getDataUsingMethod',
                'getAttributeCode',
                'getFrontendLabel',
                'isScopeGlobal',
                'getBackendType',
                'getFrontendInput',
                'getAttributesByCode'
            ]
        );

        $this->productResource->expects($this->any())->method('loadAllAttributes')
            ->willReturnSelf();
        $this->productResource->expects($this->any())->method('getAttributesByCode')
            ->willReturn([$this->eavAttributeResource]);
        $this->eavAttributeResource->expects($this->any())->method('isAllowedForRuleCondition')
            ->willReturn(false);
        $this->eavAttributeResource->expects($this->any())->method('getAttributesByCode')
            ->willReturn(false);
        $this->eavAttributeResource->expects($this->any())->method('getAttributeCode')
            ->willReturn('1');
        $this->eavAttributeResource->expects($this->any())->method('getFrontendLabel')
            ->willReturn('attribute_label');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->product = $this->objectManagerHelper->getObject(
            \Magento\CatalogRule\Model\Rule\Condition\Product::class,
            [
                'config' => $this->config,
                'product' => $this->productModel,
                'productResource' => $this->productResource,
                'productCategoryList' => $this->productCategoryList
            ]
        );
    }

    /**
     * @return void
     */
    public function testValidateMeetsCategory()
    {
        $categoryIdList = [1, 2, 3];

        $this->productCategoryList->method('getCategoryIds')->willReturn($categoryIdList);
        $this->product->setData('attribute', 'category_ids');
        $this->product->setData('value_parsed', '1');
        $this->product->setData('operator', '{}');

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
            ->willReturn($this->eavAttributeResource);

        $this->eavAttributeResource->expects($this->any())->method('isScopeGlobal')
            ->willReturn(false);
        $this->eavAttributeResource->expects($this->any())->method($input['method'])
            ->willReturn($input['type']);

        $this->productModel->expects($this->any())->method('hasData')
            ->willReturn(true);
        $this->productModel->expects($this->at(0))->method('getData')
            ->willReturn(['1' => ['1' => $attributeValue]]);
        $this->productModel->expects($this->any())->method('getData')
            ->willReturn($newValue);
        $this->productModel->expects($this->any())->method('getId')
            ->willReturn('1');
        $this->productModel->expects($this->once())->method('getStoreId')
            ->willReturn('1');
        $this->productModel->expects($this->any())->method('getResource')
            ->willReturn($this->productResource);

        $this->productResource->expects($this->any())->method('getAttribute')
            ->willReturn($this->eavAttributeResource);

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

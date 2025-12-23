<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Rule\Condition;

use Magento\Catalog\Model\ProductCategoryList;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @var Product
     */
    protected $product;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var \Magento\Catalog\Model\Product|MockObject
     */
    protected $productModel;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product|MockObject
     */
    protected $productResource;

    /**
     * @var Attribute|MockObject
     */
    protected $eavAttributeResource;

    /**
     * @var ProductCategoryList|MockObject
     */
    private $productCategoryList;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->config = $this->createPartialMock(Config::class, ['getAttribute']);
        $this->productModel = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->addMethods(['addAttributeToSelect', 'getAttributesByCode'])
            ->onlyMethods(['__wakeup', 'hasData', 'getData', 'getId', 'getStoreId', 'getResource'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->productCategoryList = $this->getMockBuilder(ProductCategoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productResource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product::class)
            ->onlyMethods(
                [
                    'loadAllAttributes',
                    'getAttributesByCode',
                    'getAttribute',
                    'getConnection',
                    'getTable'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavAttributeResource = $this->getMockBuilder(Attribute::class)
            ->addMethods(['getFrontendLabel', 'getAttributesByCode'])
            ->onlyMethods([
                '__wakeup',
                'isAllowedForRuleCondition',
                'getDataUsingMethod',
                'getAttributeCode',
                'isScopeGlobal',
                'getBackendType',
                'getFrontendInput'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->productResource->expects($this->any())->method('loadAllAttributes')->willReturnSelf();
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
            Product::class,
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
    public function testValidateMeetsCategory(): void
    {
        $categoryIdList = [1, 2, 3];

        $this->productCategoryList->method('getCategoryIds')->willReturn($categoryIdList);
        $this->product->setData('attribute', 'category_ids');
        $this->product->setData('value_parsed', '1');
        $this->product->setData('operator', '{}');

        $this->assertTrue($this->product->validate($this->productModel));
    }

    /**
     * @param string $attributeValue
     * @param string|array $parsedValue
     * @param string $newValue
     * @param string $operator
     * @param array $input
     *
     * @return void
     * @dataProvider validateDataProvider
     */
    public function testValidateWithDatetimeValue($attributeValue, $parsedValue, $newValue, $operator, $input): void
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
        $this->productModel
            ->method('getData')
            ->willReturnOnConsecutiveCalls(['1' => ['1' => $attributeValue]], $newValue, $newValue);
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
    public function testValidateWithNoValue(): void
    {
        $this->product->setData('attribute', 'color');
        $this->product->setData('value_parsed', '1');
        $this->product->setData('operator', '!=');

        $this->productModel->expects($this->atLeastOnce())
            ->method('getData')
            ->with('color')
            ->willReturn(null);
        $this->productModel->expects($this->any())
            ->method('getId')
            ->willReturn('1');
        $this->productModel->expects($this->any())
            ->method('getStoreId')
            ->willReturn('1');
        $this->assertFalse($this->product->validate($this->productModel));
    }

    /**
     * Test validation with store-scoped attribute value set only at store view level
     *
     * @return void
     */
    public function testValidateWithStoreScopedAttributeValue(): void
    {
        $attributeCode = 'special_price';
        $storeId = 2;
        $productId = '123';
        $storeSpecificValue = '40.00';
        $conditionValue = '30.00';

        $this->product->setData('attribute', $attributeCode);
        $this->product->setData('value_parsed', $conditionValue);
        $this->product->setData('operator', '>=');

        $this->config->expects($this->any())
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, $attributeCode)
            ->willReturn($this->eavAttributeResource);

        $this->eavAttributeResource->expects($this->any())
            ->method('isScopeGlobal')
            ->willReturn(false);
        $this->eavAttributeResource->expects($this->any())
            ->method('getBackendType')
            ->willReturn('decimal');
        $this->eavAttributeResource->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn('price');

        $this->productModel->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $this->productModel->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->productModel->expects($this->any())
            ->method('getResource')
            ->willReturn($this->productResource);

        $this->productModel->expects($this->exactly(2))
            ->method('getData')
            ->with($attributeCode)
            ->willReturnOnConsecutiveCalls(null, $storeSpecificValue);

        $this->productModel->expects($this->any())
            ->method('hasData')
            ->willReturn(false);

        $this->productResource->expects($this->any())
            ->method('getAttribute')
            ->with($attributeCode)
            ->willReturn($this->eavAttributeResource);

        $productCollection = $this->createMock(Collection::class);
        $productCollection->expects($this->any())
            ->method('addAttributeToSelect')
            ->with($attributeCode, 'left')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('getAllAttributeValues')
            ->with($attributeCode)
            ->willReturn([
                $productId => [
                    $storeId => $storeSpecificValue,
                ]
            ]);

        $this->product->collectValidatedAttributes($productCollection);

        $this->assertTrue($this->product->validate($this->productModel));
    }

    /**
     * Test validation with store-scoped attribute value when value is null at all scopes
     *
     * @return void
     */
    public function testValidateWithStoreScopedAttributeNoValueAtAnyScope(): void
    {
        $attributeCode = 'special_price';
        $storeId = 2;
        $productId = '123';

        $this->product->setData('attribute', $attributeCode);
        $this->product->setData('value_parsed', '30.00');
        $this->product->setData('operator', '>=');

        $this->config->expects($this->any())
            ->method('getAttribute')
            ->willReturn($this->eavAttributeResource);

        $this->eavAttributeResource->expects($this->any())
            ->method('isScopeGlobal')
            ->willReturn(false);
        $this->eavAttributeResource->expects($this->any())
            ->method('getBackendType')
            ->willReturn('decimal');

        $this->productModel->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $this->productModel->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->productModel->expects($this->any())
            ->method('getResource')
            ->willReturn($this->productResource);

        $this->productModel->expects($this->exactly(2))
            ->method('getData')
            ->with($attributeCode)
            ->willReturn(null);

        $this->productResource->expects($this->any())
            ->method('getAttribute')
            ->willReturn($this->eavAttributeResource);

        $reflection = new \ReflectionClass($this->product);
        $property = $reflection->getProperty('_entityAttributeValues');
        $property->setValue($this->product, [
            $productId => []
        ]);

        $this->assertFalse($this->product->validate($this->productModel));
    }

    /**
     * @return array
     */
    public static function validateDataProvider(): array
    {
        return [
            [
                'attributeValue' => '12:12',
                'parsedValue' => '12:12',
                'newValue' => '12:13',
                'operator' => '>=',
                'input' => ['method' => 'getBackendType', 'type' => 'input_type']
            ],
            [
                'attributeValue' => '1',
                'parsedValue' => '1',
                'newValue' => '2',
                'operator' => '>=',
                'input' => ['method' => 'getBackendType', 'type' => 'input_type']
            ],
            [
                'attributeValue' => '1',
                'parsedValue' => ['1' => '0'],
                'newValue' => ['1' => '1'],
                'operator' => '!()',
                'input' => ['method' => 'getFrontendInput', 'type' => 'multiselect']
            ]
        ];
    }
}

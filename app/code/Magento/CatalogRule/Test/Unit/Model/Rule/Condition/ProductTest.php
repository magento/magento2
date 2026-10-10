<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Rule\Condition;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Attribute\Backend\Stock;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    use MockCreationTrait;

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
     * @var ProductModel|MockObject
     */
    protected $productModel;

    /**
     * @var ProductResource|MockObject
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
        $this->productModel = $this->createPartialMockWithReflection(
            ProductModel::class,
            [
                'addAttributeToSelect',
                'getAttributesByCode',
                '__wakeup',
                'hasData',
                'getData',
                'getId',
                'getStoreId',
                'getResource'
            ]
        );

        $this->productCategoryList = $this->createMock(ProductCategoryList::class);

        $this->productResource = $this->createPartialMock(
            ProductResource::class,
            [
                'loadAllAttributes',
                'getAttributesByCode',
                'getAttribute',
                'getConnection',
                'getTable'
            ]
        );

        $this->eavAttributeResource = $this->createPartialMockWithReflection(
            Attribute::class,
            [
                'getFrontendLabel',
                'getAttributesByCode',
                '__wakeup',
                'isAllowedForRuleCondition',
                'getDataUsingMethod',
                'getAttributeCode',
                'isScopeGlobal',
                'getBackendType',
                'getFrontendInput'
            ]
        );

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
     */
    #[DataProvider('validateDataProvider')]
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
        
        $callCount = 0;
        $this->productModel
            ->method('getData')
            ->willReturnCallback(function () use (&$callCount, $attributeValue, $newValue) {
                $callCount++;
                if ($callCount === 1) {
                    return ['1' => ['1' => $attributeValue]];
                }
                return $newValue;
            });
        
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
     * @param array|null $storedValue
     * @param array $stockData
     * @param string $operator
     * @param string $conditionValue
     * @param bool $expected
     * @return void
     */
    #[DataProvider('validateStockStatusDataProvider')]
    public function testValidateUsesStockItemForStockStatusAttribute(
        ?array $storedValue,
        array $stockData,
        string $operator,
        string $conditionValue,
        bool $expected
    ): void {
        $attributeCode = 'quantity_and_stock_status';
        $this->product->setData('attribute', $attributeCode);
        $this->product->setData('value_parsed', $conditionValue);
        $this->product->setData('operator', $operator);

        $backend = $this->createPartialMockWithReflection(Stock::class, ['afterLoad']);
        $backend->method('afterLoad')->willReturnCallback(
            function (AbstractModel $object) use ($attributeCode, $stockData, $backend) {
                $object->setData($attributeCode, $stockData);
                return $backend;
            }
        );
        $attribute = $this->createPartialMockWithReflection(Attribute::class, ['getBackendModel', 'getBackend']);
        $attribute->method('getBackendModel')->willReturn(Stock::class);
        $attribute->method('getBackend')->willReturn($backend);

        $resource = $this->createPartialMock(ProductResource::class, ['getAttribute']);
        $resource->method('getAttribute')->with($attributeCode)->willReturn($attribute);

        $model = $this->createPartialMockWithReflection(
            AbstractModel::class,
            ['getResource', 'getId', 'getStoreId']
        );
        $model->method('getResource')->willReturn($resource);
        $model->method('getId')->willReturn(5);
        $model->method('getStoreId')->willReturn(1);
        if ($storedValue !== null) {
            $model->setData($attributeCode, $storedValue);
        }

        $this->assertSame($expected, $this->product->validate($model));
        $this->assertSame($storedValue, $model->getData($attributeCode));
    }

    /**
     * @return array
     */
    public static function validateStockStatusDataProvider(): array
    {
        $inStock = ['is_in_stock' => true, 'qty' => 10];
        $outOfStock = ['is_in_stock' => false, 'qty' => 0];

        return [
            'in stock, nothing stored' => [null, $inStock, '==', '1', true],
            'in stock, nothing stored, out of stock rule' => [null, $inStock, '==', '0', false],
            'out of stock, nothing stored' => [null, $outOfStock, '==', '0', true],
            'in stock, loaded array value' => [$inStock, $inStock, '==', '1', true],
            'out of stock, loaded array value' => [$outOfStock, $outOfStock, '==', '1', false],
            'in stock, is not out of stock' => [null, $inStock, '!=', '0', true],
        ];
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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogWidget\Test\Unit\Model\Rule\Condition;

use Magento\Catalog\Model\ProductCategoryList;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;
use Magento\CatalogWidget\Model\Rule\Condition\Product as ProductWidget;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends TestCase
{
    /**
     * @var ProductWidget
     */
    private $model;

    /**
     * @var MockObject
     */
    private $attributeMock;

    /**
     * @var Product|MockObject
     */
    private $productResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $eavConfig = $this->createMock(Config::class);
        $this->attributeMock = $this->createMock(Attribute::class);
        $eavConfig->expects($this->any())->method('getAttribute')->willReturn($this->attributeMock);
        $ruleMock = $this->createMock(Rule::class);
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($storeMock);
        $storeMock->method('getId')
            ->willReturn(1);
        $this->productResource = $this->createMock(Product::class);
        $this->productResource->expects($this->once())->method('loadAllAttributes')->willReturnSelf();
        $this->productResource->expects($this->once())->method('getAttributesByCode')->willReturn([]);
        $connection = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(['_connect'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productResource->method('getConnection')->willReturn($connection);
        $productCategoryList = $this->getMockBuilder(ProductCategoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            ProductWidget::class,
            [
                'config' => $eavConfig,
                'storeManager' => $storeManager,
                'productResource' => $this->productResource,
                'productCategoryList' => $productCategoryList,
                'data' => [
                    'rule' => $ruleMock,
                    'id' => 1
                ]
            ]
        );
    }

    /**
     * Test addToCollection method.
     *
     * @return void
     */
    public function testAddToCollection()
    {
        $collectionMock = $this->createMock(Collection::class);
        $selectMock = $this->createMock(Select::class);
        $collectionMock->expects($this->once())->method('getSelect')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('join')->willReturnSelf();
        $this->attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('code');
        $this->attributeMock->expects($this->once())->method('isStatic')->willReturn(false);
        $this->attributeMock->expects($this->once())->method('getBackend')->willReturn(true);
        $this->attributeMock->expects($this->once())->method('isScopeGlobal')->willReturn(true);
        $this->attributeMock->expects($this->once())->method('isScopeGlobal')->willReturn(true);
        $this->attributeMock->expects($this->once())->method('getBackendType')->willReturn('multiselect');

        $entityMock = $this->createMock(AbstractEntity::class);
        $entityMock->expects($this->once())->method('getLinkField')->willReturn('entitiy_id');
        $this->attributeMock->expects($this->once())->method('getEntity')->willReturn($entityMock);

        $this->model->addToCollection($collectionMock);
    }

    /**
     * Test getMappedSqlField method.
     *
     * @return void
     */
    public function testGetMappedSqlFieldSku()
    {
        $this->model->setAttribute('sku');
        $this->assertEquals('e.sku', $this->model->getMappedSqlField());
        $this->model->setAttribute('attribute_set_id');
        $this->assertEquals('e.attribute_set_id', $this->model->getMappedSqlField());
    }

    /**
     * Test getMappedSqlField method for price attribute.
     *
     * @dataProvider getMappedSqlFieldPriceDataProvider
     * @param bool $isScopeGlobal
     * @param bool $isUsingPriceIndex
     * @param string $expectedMappedField
     */
    public function testGetMappedSqlFieldPrice(
        bool $isScopeGlobal,
        bool $isUsingPriceIndex,
        string $expectedMappedField
    ): void {
        $productLimitation = new ProductLimitation();
        $productLimitation['use_price_index'] = $isUsingPriceIndex;
        $collectionMock = $this->mockCollection(
            [
                'getLimitationFilters' => $productLimitation,
                'getAllAttributeValues' => [
                   1 => [
                       0 => 10,
                       1 => 11
                   ]
                ]
            ]
        );
        $this->mockAttribute(
            [
                'getAttributeCode' => 'price',
                'isScopeGlobal' => $isScopeGlobal,
                'getBackendType' => 'decimal'
            ]
        );
        $this->model->setAttribute('price');
        $this->model->setValue(10);
        $this->model->setOperator('>=');
        $this->model->collectValidatedAttributes($collectionMock);
        $this->assertEquals($expectedMappedField, $this->model->getMappedSqlField());
    }

    /**
     * @return array
     */
    public static function getMappedSqlFieldPriceDataProvider(): array
    {
        return [
            [
                true,
                true,
                'price_index.min_price'
            ],
            [
                true,
                false,
                'at_price.value'
            ],
            [
                false,
                true,
                'price_index.min_price'
            ],
            [
                false,
                false,
                'at_price.value'
            ],
            [
                false,
                true,
                'price_index.min_price'
            ],
        ];
    }

    /**
     * @param array $conditionArray
     * @param array $attributeConfig
     * @param array $attributeValues
     * @param mixed $expected
     * @dataProvider getBindArgumentValueDataProvider
     */
    public function testGetBindArgumentValue(
        array $conditionArray,
        array $attributeConfig,
        array $attributeValues,
        $expected
    ): void {
        $conditionArray['type'] = ProductWidget::class;
        $attributeConfig['getAttributeCode'] = $conditionArray['attribute'];
        $collectionMock = $this->mockCollection(
            [
                'getAllAttributeValues' => $attributeValues,
            ]
        );
        $this->mockAttribute($attributeConfig);
        $this->model->loadArray($conditionArray);
        $this->model->collectValidatedAttributes($collectionMock);
        $this->assertEquals($expected, $this->model->getBindArgumentValue());
    }

    /**
     * @return array
     */
    public static function getBindArgumentValueDataProvider(): array
    {
        return [
            [
                [
                    'attribute' => 'attr_1',
                    'value' => '2',
                    'operator' => '==',
                ],
                [
                    'isScopeGlobal' => false,
                    'getBackendType' => 'int'
                ],
                [
                    1 => [
                        0 => 1,
                        1 => 2
                    ],
                    2 => [
                        0 => 1
                    ],
                    3 => [
                        1 => 2
                    ]
                ],
                '2'
            ],
            [
                [
                    'attribute' => 'attr_1',
                    'value' => '2',
                    'operator' => '==',
                ],
                [
                    'isScopeGlobal' => true,
                    'getBackendType' => 'int'
                ],
                [
                    1 => [
                        0 => 1,
                        1 => 2
                    ],
                    2 => [
                        0 => 1
                    ],
                    3 => [
                        1 => 2
                    ]
                ],
                '2'
            ],
        ];
    }

    /**
     * @param array $configuration
     */
    private function mockAttribute(array $configuration = []): void
    {
        $defaultConfiguration = [
            'getAttributeCode' => 'code',
            'isStatic' => false,
            'getBackend' => true,
            'isScopeGlobal' => true,
            'getBackendType' => 'int',
        ];
        $configuration = array_merge($defaultConfiguration, $configuration);
        $this->attributeMock->method('getAttributeCode')
            ->willReturn($configuration['getAttributeCode']);
        $this->attributeMock->method('isStatic')
            ->willReturn($configuration['isStatic']);
        $this->attributeMock->method('getBackend')
            ->willReturn($configuration['getBackend']);
        $this->attributeMock->method('isScopeGlobal')
            ->willReturn($configuration['isScopeGlobal']);
        $this->attributeMock->method('getBackendType')
            ->willReturn($configuration['getBackendType']);
        $entityMock = $this->createMock(AbstractEntity::class);
        $entityMock->method('getLinkField')
            ->willReturn('entitiy_id');
        $this->attributeMock->method('getEntity')
            ->willReturn($entityMock);
    }

    /**
     * @param array $configuration
     * @return Collection
     */
    private function mockCollection(array $configuration = []): Collection
    {
        $collectionMock = $this->createConfiguredMock(Collection::class, $configuration);
        $selectMock = $this->createMock(Select::class);
        $collectionMock->method('getSelect')
            ->willReturn($selectMock);
        return $collectionMock;
    }
}

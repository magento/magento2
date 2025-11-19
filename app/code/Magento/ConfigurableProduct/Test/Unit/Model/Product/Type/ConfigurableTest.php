<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ArrayIterator;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Collection\SalableProcessor;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\CollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection as ProductCollection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory
    as ProductCollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Quote\Model\Quote\Item\Option;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
#[CoversClass(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class)]
class ConfigurableTest extends TestCase
{
    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var SalableProcessor|MockObject
     */
    private $salableProcessor;

    /**
     * @var array
     */
    private $attributeData = [
        1 => [
            'id' => 1,
            'code' => 'someattr',
            'attribute_id' => 111,
            'position' => 0,
            'label' => 'Some Super Attribute',
            'values' => []
        ]
    ];

    /**
     * @var MockObject
     */
    private $eavConfig;

    /**
     * @var Configurable
     */
    private $model;

    /**
     * @var MockObject
     */
    private $configurableAttributeFactoryMock;

    /**
     * @var MockObject
     */
    private $typeConfigurableFactory;

    /**
     * @var MockObject
     */
    private $attributeCollectionFactory;

    /**
     * @var MockObject
     */
    private $productCollectionFactory;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * @var ObjectManager
     */
    private $objectHelper;

    /**
     * @var JoinProcessorInterface|MockObject
     */
    private $extensionAttributesJoinProcessorMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var EntityMetadata|MockObject
     */
    private $entityMetadata;

    /**
     * @var MockObject
     */
    private $cache;

    /**
     * @var MockObject
     */
    private $serializer;

    /**
     * @var Config
     */
    private $catalogConfig;

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectHelper = new ObjectManager($this);
        $eventManager = $this->createMock(ManagerInterface::class);
        $fileStorageDbMock = $this->createMock(Database::class);
        $filesystem = $this->createMock(Filesystem::class);
        $coreRegistry = $this->createMock(Registry::class);
        $logger = $this->createMock(LoggerInterface::class);
        $this->typeConfigurableFactory = $this->createMock(ConfigurableFactory::class);
        $configurableResourceMock = $this->createMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class
        );
        $this->typeConfigurableFactory->method('create')->willReturn($configurableResourceMock);
        $this->configurableAttributeFactoryMock = $this->createPartialMock(AttributeFactory::class, ['create']);
        $this->productCollectionFactory = $this->createPartialMock(ProductCollectionFactory::class, ['create']);
        $this->attributeCollectionFactory = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->extensionAttributesJoinProcessorMock = $this->createMock(JoinProcessorInterface::class);
        $this->entityMetadata = $this->createMock(EntityMetadata::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->cache = $this->createMock(FrontendInterface::class);
        $this->catalogConfig = $this->createMock(Config::class);
        $this->eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->serializer = $this->createMock(Json::class);

        $this->metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->entityMetadata);
        $this->productFactory = $this->createPartialMock(ProductInterfaceFactory::class, ['create']);

        $this->salableProcessor = $this->createMock(SalableProcessor::class);

        $this->model = $this->objectHelper->getObject(
            Configurable::class,
            [
                'eavConfig' => $this->eavConfig,
                'typeConfigurableFactory' => $this->typeConfigurableFactory,
                'configurableAttributeFactory' => $this->configurableAttributeFactoryMock,
                'productCollectionFactory' => $this->productCollectionFactory,
                'attributeCollectionFactory' => $this->attributeCollectionFactory,
                'eventManager' => $eventManager,
                'fileStorageDb' => $fileStorageDbMock,
                'filesystem' => $filesystem,
                'coreRegistry' => $coreRegistry,
                'logger' => $logger,
                'productRepository' => $this->productRepository,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'customerSession' => $this->createMock(Session::class),
                'cache' => $this->cache,
                'catalogConfig' => $this->catalogConfig,
                'serializer' => $this->serializer,
                'salableProcessor' => $this->salableProcessor,
                'metadataPool' => $this->metadataPool,
                'productFactory' => $this->productFactory
            ]
        );
        $refClass = new ReflectionClass(Configurable::class);
        $refProperty = $refClass->getProperty('metadataPool');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->model, $this->metadataPool);
    }

    /**
     * @return void
     */
    public function testHasWeightTrue(): void
    {
        $this->assertTrue($this->model->hasWeight(), 'This product has not weight, but it should');
    }

    /**
     * @return void
     */
    public function testSave(): void
    {
        $product = new \Magento\Catalog\Test\Unit\Helper\ProductTestHelper();
        // Configure ProductTestHelper with expected values
        $product->setConfigurableAttributesData($this->attributeData);
        $product->setIsDuplicate(true);
        $product->setStoreId(1);
        $product->setAssociatedProductIds([2]);
        $product->setHasData('_cache_instance_used_product_attribute_ids', true);
        $extensionAttributes = new \Magento\Catalog\Test\Unit\Helper\ProductExtensionTestHelper();
        $extensionAttributes->setConfigurableProductOptions([]);
        $extensionAttributes->setConfigurableProductLinks([]);

        $this->entityMetadata->method('getLinkField')->willReturn('link');
        // Configure ProductTestHelper with extension attributes
        $product->setExtensionAttributes($extensionAttributes);
        $product->setData('_cache_instance_used_product_attribute_ids', 1);
        $product->setData('link', 1);

        $attribute = new \Magento\Eav\Test\Unit\Helper\AttributeTestHelper();
        $expectedAttributeData = $this->attributeData[1];
        unset($expectedAttributeData['id']);
        // Configure AttributeTestHelper - addData, setStoreId, setProductId, save methods return $this

        $this->configurableAttributeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($attribute);
        $attributeCollection = $this->createMock(Collection::class);
        $this->attributeCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($attributeCollection);
        // typeConfigurableFactory anonymous class returns $this for create() and saveProducts()

        $this->model->save($product);
    }

    /**
     * @return void
     */
    public function testGetRelationInfo(): void
    {
        $info = $this->model->getRelationInfo();
        $this->assertInstanceOf(DataObject::class, $info);
        $this->assertEquals('catalog_product_super_link', $info->getData('table'));
        $this->assertEquals('parent_id', $info->getData('parent_field_name'));
        $this->assertEquals('product_id', $info->getData('child_field_name'));
    }

    /**
     * @return void
     */
    public function testCanUseAttribute(): void
    {
        $attribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $attribute->expects($this->once())
            ->method('getIsGlobal')
            ->willReturn(1);
        $attribute->expects($this->once())
            ->method('getIsVisible')
            ->willReturn(1);
        $attribute->expects($this->once())
            ->method('usesSource')
            ->willReturn(1);
        $attribute->expects($this->once())
            ->method('getIsUserDefined')
            ->willReturn(1);

        $this->assertTrue($this->model->canUseAttribute($attribute));
    }

    /**
     * @return void
     */
    public function testGetUsedProducts(): void
    {
        $productCollectionItem = $this->createMock(Product::class);
        $attributeCollection = $this->createMock(Collection::class);
        $product = $this->createMock(Product::class);
        $productCollection = $this->createMock(ProductCollection::class);

        $attributeCollection->expects($this->any())->method('setProductFilter')->willReturnSelf();
        $product->expects($this->atLeastOnce())->method('getStoreId')->willReturn(5);

        $product->expects($this->exactly(2))
            ->method('hasData')
            ->willReturnMap(
                [
                    ['_cache_instance_products', null],
                    ['_cache_instance_used_product_attributes', 1]
                ]
            );
        $product->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    ['_cache_instance_used_product_attributes', null, []]
                ]
            );
        $this->catalogConfig->method('getProductAttributes')->willReturn([]);
        $productCollection->expects($this->atLeastOnce())->method('addAttributeToSelect')->willReturnSelf();
        $productCollection->expects($this->once())->method('setProductFilter')->willReturnSelf();
        $productCollection->expects($this->atLeastOnce())->method('setFlag')->willReturnSelf();
        $productCollection->expects($this->once())->method('addTierPriceData')->willReturnSelf();
        $productCollection->expects($this->once())->method('addFilterByRequiredOptions')->willReturnSelf();
        $productCollection->expects($this->once())->method('setStoreId')->with(5)->willReturn([]);
        $productCollection->expects($this->once())->method('getItems')->willReturn([$productCollectionItem]);

        $this->productCollectionFactory->method('create')->willReturn($productCollection);
        $this->model->getUsedProducts($product);
    }

    /**
     * @param int $productStore
     *
     * @return void
     */
    #[DataProvider('getConfigurableAttributesAsArrayDataProvider')]
    public function testGetConfigurableAttributesAsArray($productStore): void
    {
        $attributeSource = $this->createMock(AbstractSource::class);
        $attributeFrontend = $this->createMock(AbstractFrontend::class);
        $eavAttribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);

        $attributeSource->expects($this->once())->method('getAllOptions')->willReturn([]);
        $attributeFrontend->expects($this->once())->method('getLabel')->willReturn('Label');
        $eavAttribute->expects($this->once())->method('getFrontend')->willReturn($attributeFrontend);
        $eavAttribute->expects($this->once())->method('getSource')->willReturn($attributeSource);
        $eavAttribute->expects($this->atLeastOnce())->method('getStoreLabel')->willReturn('Store Label');

        $attribute = new \Magento\Eav\Test\Unit\Helper\AttributeTestHelper();
        $attribute->setProductAttribute($eavAttribute);

        $product = $this->createPartialMock(Product::class, ['getStoreId', 'getData', 'hasData', '__sleep']);
        $product->expects($this->atLeastOnce())->method('getStoreId')->willReturn($productStore);
        $product->expects($this->atLeastOnce())->method('hasData')
            ->willReturnMap(
                [
                    ['_cache_instance_configurable_attributes', 1]
                ]
            );
        $product->expects($this->any())->method('getData')
            ->willReturnMap(
                [
                    ['_cache_instance_configurable_attributes', null, [$attribute]]
                ]
            );

        $result = $this->model->getConfigurableAttributesAsArray($product);
        $this->assertCount(1, $result);
    }

    /**
     * @return array
     */
    public static function getConfigurableAttributesAsArrayDataProvider(): array
    {
        return [
            [5],
            [null],
        ];
    }

    /**
     * @return void
     */
    public function testGetConfigurableAttributesNewProduct(): void
    {
        $configurableAttributes = '_cache_instance_configurable_attributes';

        /** @var Product|MockObject $product */
        $product = $this->createPartialMock(Product::class, ['hasData', 'getId']);

        $product->expects($this->once())->method('hasData')->with($configurableAttributes)->willReturn(false);
        $product->expects($this->once())->method('getId')->willReturn(null);

        $this->assertEquals([], $this->model->getConfigurableAttributes($product));
    }

    /**
     * @return void
     */
    public function testGetConfigurableAttributes(): void
    {
        $configurableAttributes = '_cache_instance_configurable_attributes';

        /** @var Product|MockObject $product */
        $product = $this->createPartialMock(Product::class, ['getData', 'hasData', 'setData', 'getId']);

        $product->expects($this->once())->method('hasData')->with($configurableAttributes)->willReturn(false);
        $product->expects($this->once())->method('getId')->willReturn(1);

        $attributeCollection = $this->createPartialMock(
            Collection::class,
            ['setProductFilter', 'orderByPosition', 'load']
        );
        $attributeCollection->expects($this->once())->method('setProductFilter')->willReturnSelf();
        $attributeCollection->expects($this->once())->method('orderByPosition')->willReturnSelf();
        $attributeCollection->expects($this->once())->method('load')->willReturnSelf();

        $this->attributeCollectionFactory->expects($this->once())->method('create')->willReturn($attributeCollection);

        $product->expects($this->once())
            ->method('setData')
            ->with($configurableAttributes, $attributeCollection)
            ->willReturnSelf();

        $product->expects($this->once())
            ->method('getData')
            ->with($configurableAttributes)
            ->willReturn($attributeCollection);

        $this->assertEquals($attributeCollection, $this->model->getConfigurableAttributes($product));
    }

    /**
     * @return void
     */
    public function testResetConfigurableAttributes(): void
    {
        $product = $this->createPartialMock(Product::class, ['unsetData']);
        $product->expects($this->once())
            ->method('unsetData')
            ->with('_cache_instance_configurable_attributes')
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->resetConfigurableAttributes($product));
    }

    /**
     * @return void
     */
    public function testHasOptions(): void
    {
        $productMock = $this->createPartialMock(Product::class, ['getOptions']);
        $productMock->expects($this->once())->method('getOptions')->willReturn([true]);

        $this->assertTrue($this->model->hasOptions($productMock));
    }

    /**
     * @return void
     */
    public function testHasOptionsConfigurableAttribute(): void
    {
        $productMock = new \Magento\Catalog\Test\Unit\Helper\ProductTestHelper();
        $attributeMock = $this->createMock(Attribute::class);

        // Configure ProductTestHelper with expected values
        $productMock->setOptions([]);
        $productMock->setHasData('_cache_instance_configurable_attributes', 1);
        $productMock->setData('_cache_instance_configurable_attributes', [$attributeMock]);

        $this->assertTrue($this->model->hasOptions($productMock));
    }

    /**
     * @return void
     */
    public function testHasOptionsFalse(): void
    {
        $productMock = $this->createPartialMock(Product::class, ['getOptions', 'hasData', 'getData']);

        $productMock->expects($this->once())->method('getOptions')->willReturn([]);
        $productMock->expects($this->once())
            ->method('hasData')
            ->with('_cache_instance_configurable_attributes')->willReturn(1);
        $productMock->expects($this->once())
            ->method('getData')
            ->with('_cache_instance_configurable_attributes')->willReturn([]);

        $this->assertFalse($this->model->hasOptions($productMock));
    }

    /**
     * @return void
     */
    public function testIsSalable(): void
    {
        $productMock = $this->createPartialMock(
            Product::class,
            ['getStatus', 'hasData', 'getData', 'getStoreId', 'setData', 'getSku']
        );
        $productMock->expects($this->once())->method('getStatus')->willReturn(1);
        $productMock->method('hasData')->willReturn(true);
        $productMock
            ->method('getData')
            ->willReturnCallback(function ($arg) {
                static $callCount = 0;
                $callCount++;
                if ($arg == '_cache_instance_store_filter') {
                    return $callCount === 1 ? 0 : null;
                } elseif ($arg == 'is_salable') {
                    return $callCount === 2 ? true : null;
                }
            });
        $productMock
            ->method('getSku')
            ->willReturn('SKU-CODE');
        $productCollection = $this->createPartialMock(
            ProductCollection::class,
            ['setFlag', 'setProductFilter', 'addStoreFilter', 'getSize']
        );
        $productCollection->expects($this->any())->method('setFlag')->willReturnSelf();
        $productCollection
            ->expects($this->once())
            ->method('setProductFilter')
            ->with($productMock)->willReturnSelf();
        $productCollection
            ->expects($this->once())
            ->method('addStoreFilter')->willReturnSelf();
        $productCollection
            ->expects($this->once())
            ->method('getSize')
            ->willReturn(1);
        $this->salableProcessor
            ->expects($this->once())
            ->method('process')
            ->with($productCollection)
            ->willReturn($productCollection);
        $this->productCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($productCollection);
        $this->assertTrue($this->model->isSalable($productMock));
    }

    /**
     * @return void
     */
    public function testGetSelectedAttributesInfo(): void
    {
        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $productMock = $this->createMock(Product::class);
        $optionMock = $this->createMock(OptionInterface::class);
        $usedAttributeMock = new \Magento\Eav\Test\Unit\Helper\AttributeTestHelper();
        $attributeMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);

        $optionMock->expects($this->once())->method('getValue')->willReturn(json_encode($this->attributeData));
        $productMock->expects($this->once())->method('getCustomOption')->with('attributes')->willReturn($optionMock);
        $productMock->expects($this->once())->method('hasData')->willReturn(true);
        $productMock
            ->method('getData')
            ->willReturnOnConsecutiveCalls(true, [1 => $usedAttributeMock]);
        $usedAttributeMock->setProductAttribute($attributeMock);
        $attributeMock->expects($this->once())->method('getStoreLabel')->willReturn('attr_store_label');
        $attributeMock->expects($this->once())->method('getSourceModel')->willReturn(false);

        $this->assertEquals(
            $this->model->getSelectedAttributesInfo($productMock),
            [
                [
                    'label' => 'attr_store_label',
                    'value' => '',
                    'option_id' => 1,
                    'option_value' => ''
                ]
            ]
        );
    }

    /**
     *
     * @return void
     */
    public function testCheckProductBuyState(): void
    {
        $productMock = new \Magento\Catalog\Test\Unit\Helper\ProductTestHelper();
        $optionMock = $this->createMock(Option::class);

        // Configure ProductTestHelper with expected values
        $productMock->setSkipCheckRequiredOption(true);
        $productMock->setCustomOption('info_buyRequest', $optionMock);
        $optionMock->expects($this->once())
            ->method('getValue')
            ->willReturn(json_encode(['super_attribute' => ['test_key' => 'test_value', 'empty_key' => '']]));
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->assertEquals($this->model, $this->model->checkProductBuyState($productMock));
    }

    /**
     * @return void
     */
    public function testCheckProductBuyStateException(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('You need to choose options for your item.');
        $productMock = new \Magento\Catalog\Test\Unit\Helper\ProductTestHelper();
        $optionMock = $this->createMock(Option::class);

        // Configure ProductTestHelper with expected values
        $productMock->setSkipCheckRequiredOption(true);
        $productMock->setCustomOption('info_buyRequest', $optionMock);
        $optionMock->expects($this->once())->method('getValue')->willReturn(json_encode([]));
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->model->checkProductBuyState($productMock);
    }

    /**
     * @return void
     */
    public function testGetProductByAttributesReturnUsedProduct(): void
    {
        $productMock = $this->createMock(Product::class);
        $firstItemMock = $this->createMock(Product::class);
        $usedProductMock = $this->createMock(Product::class);
        $eavAttributeMock = $this->createMock(AbstractAttribute::class);
        $productCollection = $this->createMock(ProductCollection::class);

        $this->productCollectionFactory->expects($this->once())->method('create')->willReturn($productCollection);
        $productCollection->expects($this->once())->method('setProductFilter')->willReturnSelf();
        $productCollection->expects($this->once())->method('setFlag')->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToSelect')->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToFilter')->willReturnSelf();
        $productCollection->expects($this->once())->method('getFirstItem')->willReturn($firstItemMock);
        $productCollection->expects($this->once())->method('getIterator')->willReturn(
            new ArrayIterator([$usedProductMock])
        );

        $firstItemMock->expects($this->once())->method('getId')->willReturn(false);
        $productMock
            ->method('getData')
            ->with('_cache_instance_store_filter')
            ->willReturn('some_filter');
        $productMock->method('hasData')->willReturn(true);

        $eavAttributeMock->expects($this->once())->method('getAttributeCode')->willReturn('attr_code');
        $usedProductMock->expects($this->once())
            ->method('getData')->with('attr_code')
            ->willReturn($this->attributeData[1]);
        $this->eavConfig->method('getAttribute')->willReturn($eavAttributeMock);

        $this->assertEquals(
            $usedProductMock,
            $this->model->getProductByAttributes($this->attributeData, $productMock)
        );
    }

    /**
     * @return void
     */
    public function testGetProductByAttributesReturnFirstItem(): void
    {
        $productMock = $this->createMock(Product::class);
        $firstItemMock = $this->createMock(Product::class);
        $productCollection = $this->createMock(ProductCollection::class);

        $this->productCollectionFactory->method('create')->willReturn($productCollection);
        $productCollection->expects($this->once())->method('setProductFilter')->willReturnSelf();
        $productCollection->expects($this->once())->method('setFlag')->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToSelect')->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToFilter')->willReturnSelf();
        $productCollection->expects($this->once())->method('getFirstItem')->willReturn($firstItemMock);
        $firstItemMock->expects($this->once())->method('getId')->willReturn(3);
        $this->productRepository->expects($this->once())->method('getById')->with(3)->willReturn($firstItemMock);

        $this->assertEquals(
            $firstItemMock,
            $this->model->getProductByAttributes($this->attributeData, $productMock)
        );
    }

    /**
     * @return void
     */
    public function testSetImageFromChildProduct(): void
    {
        $productMock = new \Magento\Catalog\Test\Unit\Helper\ProductTestHelper();
        $childProductMock = $this->createMock(Product::class);
        $this->entityMetadata->method('getLinkField')->willReturn('link');
        // Configure ProductTestHelper with expected values
        $productMock->setHasData('_cache_instance_products', true);
        $productMock->setData('image', 'no_selection');
        $productMock->setData('_cache_instance_products', [$childProductMock]);

        $childProductMock->expects($this->any())->method('getData')->with('image')->willReturn('image_data');

        $this->model->setImageFromChildProduct($productMock);
    }
}

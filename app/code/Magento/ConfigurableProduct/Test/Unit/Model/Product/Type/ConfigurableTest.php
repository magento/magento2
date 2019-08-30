<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Type;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Config;
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
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ConfigurableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var SalableProcessor|\PHPUnit_Framework_MockObject_MockObject
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
            'values' => [],
        ]
    ];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    /**
     * @var Configurable
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configurableAttributeFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $typeConfigurableFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeCollectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectHelper;

    /**
     * @var JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessorMock;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityMetadata;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var Config
     */
    private $catalogConfig;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileStorageDbMock = $this->getMockBuilder(\Magento\MediaStorage\Helper\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $coreRegistry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeConfigurableFactory = $this->getMockBuilder(ConfigurableFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'saveProducts'])
            ->getMock();
        $this->configurableAttributeFactoryMock = $this->getMockBuilder(AttributeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productCollectionFactory = $this->getMockBuilder(ProductCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->attributeCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributesJoinProcessorMock = $this->getMockBuilder(JoinProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityMetadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cache = $this->getMockBuilder(\Magento\Framework\Cache\FrontendInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->entityMetadata);
        $this->productFactory = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

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
                'customerSession' => $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock(),
                'cache' => $this->cache,
                'catalogConfig' => $this->catalogConfig,
                'serializer' => $this->serializer,
                'salableProcessor' => $this->salableProcessor,
                'metadataPool' => $this->metadataPool,
                'productFactory' => $this->productFactory,
            ]
        );
        $refClass = new \ReflectionClass(Configurable::class);
        $refProperty = $refClass->getProperty('metadataPool');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->model, $this->metadataPool);
    }

    public function testHasWeightTrue()
    {
        $this->assertTrue($this->model->hasWeight(), 'This product has not weight, but it should');
    }

    /**
     * Test `Save` method
     */
    public function testSave()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'getIsDuplicate',
                    'dataHasChangedFor',
                    'getConfigurableAttributesData',
                    'getStoreId',
                    'getId',
                    'getData',
                    'hasData',
                    'getAssociatedProductIds',
                ]
            )->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('dataHasChangedFor')->willReturn('false');
        $product->expects($this->once())
            ->method('getConfigurableAttributesData')
            ->willReturn($this->attributeData);
        $product->expects($this->once())->method('getIsDuplicate')->willReturn(true);
        $product->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $product->expects($this->once())->method('getAssociatedProductIds')->willReturn([2]);
        $product->expects($this->once())
            ->method('hasData')
            ->with('_cache_instance_used_product_attribute_ids')
            ->willReturn(true);
        $extensionAttributes = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(
                [
                    'getConfigurableProductOptions',
                    'getConfigurableProductLinks'
                ]
            )
            ->getMockForAbstractClass();
        $this->entityMetadata->expects($this->any())
            ->method('getLinkField')
            ->willReturn('link');
        $dataMap = [
            ['extension_attributes', null, $extensionAttributes],
            ['_cache_instance_used_product_attribute_ids', null, 1],
            ['link', null, 1],
        ];
        $product->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturnMap($dataMap);
        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['addData', 'setStoreId', 'setProductId', 'save', '__wakeup', '__sleep'])
            ->getMock();
        $expectedAttributeData = $this->attributeData[1];
        unset($expectedAttributeData['id']);
        $attribute->expects($this->once())->method('addData')->with($expectedAttributeData)->willReturnSelf();
        $attribute->expects($this->once())->method('setStoreId')->with(1)->willReturnSelf();
        $attribute->expects($this->once())->method('setProductId')->with(1)->willReturnSelf();
        $attribute->expects($this->once())->method('save')->willReturnSelf();

        $this->configurableAttributeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($attribute);
        $attributeCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($attributeCollection);
        $this->typeConfigurableFactory->expects($this->once())
            ->method('create')
            ->willReturnSelf();
        $this->typeConfigurableFactory->expects($this->once())
            ->method('saveProducts')
            ->willReturnSelf();

        $this->model->save($product);
    }

    public function testGetRelationInfo()
    {
        $info = $this->model->getRelationInfo();
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $info);
        $this->assertEquals('catalog_product_super_link', $info->getData('table'));
        $this->assertEquals('parent_id', $info->getData('parent_field_name'));
        $this->assertEquals('product_id', $info->getData('child_field_name'));
    }

    public function testCanUseAttribute()
    {
        $attribute = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
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

    public function testGetUsedProducts()
    {
        $productCollectionItem = $this->createMock(\Magento\Catalog\Model\Product::class);
        $attributeCollection = $this->createMock(Collection::class);
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $productCollection = $this->createMock(ProductCollection::class);

        $attributeCollection->expects($this->any())->method('setProductFilter')->willReturnSelf();
        $product->expects($this->atLeastOnce())->method('getStoreId')->willReturn(5);

        $product->expects($this->exactly(2))
            ->method('hasData')
            ->willReturnMap(
                [
                    ['_cache_instance_products', null],
                    ['_cache_instance_used_product_attributes', 1],
                ]
            );
        $product->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    ['_cache_instance_used_product_attributes', null, []]
                ]
            );
        $this->catalogConfig->expects($this->any())->method('getProductAttributes')->willReturn([]);
        $productCollection->expects($this->atLeastOnce())->method('addAttributeToSelect')->willReturnSelf();
        $productCollection->expects($this->once())->method('setProductFilter')->willReturnSelf();
        $productCollection->expects($this->atLeastOnce())->method('setFlag')->willReturnSelf();
        $productCollection->expects($this->once())->method('addTierPriceData')->willReturnSelf();
        $productCollection->expects($this->once())->method('addFilterByRequiredOptions')->willReturnSelf();
        $productCollection->expects($this->once())->method('setStoreId')->with(5)->willReturn([]);
        $productCollection->expects($this->once())->method('getItems')->willReturn([$productCollectionItem]);

        $this->productCollectionFactory->expects($this->any())->method('create')->willReturn($productCollection);
        $this->model->getUsedProducts($product);
    }

    /**
     * @param int $productStore
     *
     * @dataProvider getConfigurableAttributesAsArrayDataProvider
     */
    public function testGetConfigurableAttributesAsArray($productStore)
    {
        $attributeSource = $this->getMockBuilder(AbstractSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeFrontend = $this->getMockBuilder(AbstractFrontend::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eavAttribute = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $attributeSource->expects($this->once())->method('getAllOptions')->willReturn([]);
        $attributeFrontend->expects($this->once())->method('getLabel')->willReturn('Label');
        $eavAttribute->expects($this->once())->method('getFrontend')->willReturn($attributeFrontend);
        $eavAttribute->expects($this->once())->method('getSource')->willReturn($attributeSource);
        $eavAttribute->expects($this->atLeastOnce())->method('getStoreLabel')->willReturn('Store Label');

        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductAttribute', '__wakeup', '__sleep'])
            ->getMock();
        $attribute->expects($this->any())->method('getProductAttribute')->willReturn($eavAttribute);

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getStoreId', 'getData', 'hasData', '__wakeup', '__sleep'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->atLeastOnce())->method('getStoreId')->willReturn($productStore);
        $product->expects($this->atLeastOnce())->method('hasData')
            ->will(
                $this->returnValueMap(
                    [
                        ['_cache_instance_configurable_attributes', 1],
                    ]
                )
            );
        $product->expects($this->any())->method('getData')
            ->will(
                $this->returnValueMap(
                    [
                        ['_cache_instance_configurable_attributes', null, [$attribute]],
                    ]
                )
            );

        $result = $this->model->getConfigurableAttributesAsArray($product);
        $this->assertCount(1, $result);
    }

    /**
     * @return array
     */
    public function getConfigurableAttributesAsArrayDataProvider()
    {
        return [
            [5],
            [null],
        ];
    }

    public function testGetConfigurableAttributesNewProduct()
    {
        $configurableAttributes = '_cache_instance_configurable_attributes';

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['hasData', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $product->expects($this->once())->method('hasData')->with($configurableAttributes)->willReturn(false);
        $product->expects($this->once())->method('getId')->willReturn(null);

        $this->assertEquals([], $this->model->getConfigurableAttributes($product));
    }

    public function testGetConfigurableAttributes()
    {
        $configurableAttributes = '_cache_instance_configurable_attributes';

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getData', 'hasData', 'setData', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $product->expects($this->once())->method('hasData')->with($configurableAttributes)->willReturn(false);
        $product->expects($this->once())->method('getId')->willReturn(1);

        $attributeCollection = $this->getMockBuilder(Collection::class)
            ->setMethods(['setProductFilter', 'orderByPosition', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
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

    public function testResetConfigurableAttributes()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['unsetData'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('unsetData')
            ->with('_cache_instance_configurable_attributes')
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->resetConfigurableAttributes($product));
    }

    public function testHasOptions()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getOptions'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())->method('getOptions')->willReturn([true]);

        $this->assertTrue($this->model->hasOptions($productMock));
    }

    public function testHasOptionsConfigurableAttribute()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getAttributeCode', 'getOptions', 'hasData', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())->method('getOptions')->willReturn([]);
        $productMock->expects($this->once())
            ->method('hasData')
            ->with('_cache_instance_configurable_attributes')->willReturn(1);
        $productMock->expects($this->once())
            ->method('getData')
            ->with('_cache_instance_configurable_attributes')->willReturn([$attributeMock]);

        $this->assertTrue($this->model->hasOptions($productMock));
    }

    public function testHasOptionsFalse()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getOptions', 'hasData', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())->method('getOptions')->willReturn([]);
        $productMock->expects($this->once())
            ->method('hasData')
            ->with('_cache_instance_configurable_attributes')->willReturn(1);
        $productMock->expects($this->once())
            ->method('getData')
            ->with('_cache_instance_configurable_attributes')->willReturn([]);

        $this->assertFalse($this->model->hasOptions($productMock));
    }

    public function testIsSalable()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getStatus', 'hasData', 'getData', 'getStoreId', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())->method('getStatus')->willReturn(1);
        $productMock->expects($this->any())->method('hasData')->willReturn(true);
        $productMock->expects($this->at(2))->method('getData')->with('is_salable')->willReturn(true);
        $productCollection = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection::class
        )
            ->setMethods(
                [
                    'setFlag',
                    'setProductFilter',
                    'addStoreFilter',
                    'getSize'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects($this->any())->method('setFlag')->will($this->returnSelf());
        $productCollection
            ->expects($this->once())
            ->method('setProductFilter')
            ->with($productMock)
            ->will($this->returnSelf());
        $productCollection
            ->expects($this->once())
            ->method('addStoreFilter')
            ->will($this->returnSelf());
        $productCollection
            ->expects($this->once())
            ->method('getSize')
            ->willReturn(1);
        $this->salableProcessor
            ->expects($this->once())
            ->method('process')
            ->with($productCollection)
            ->will($this->returnValue($productCollection));
        $this->productCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($productCollection));
        $this->assertTrue($this->model->isSalable($productMock));
    }

    public function testGetSelectedAttributesInfo()
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

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock = $this->getMockBuilder(OptionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $usedAttributeMock = $this->getMockBuilder(
            Attribute::class
        )
            ->setMethods(['getProductAttribute'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $optionMock->expects($this->once())->method('getValue')->willReturn(json_encode($this->attributeData));
        $productMock->expects($this->once())->method('getCustomOption')->with('attributes')->willReturn($optionMock);
        $productMock->expects($this->once())->method('hasData')->willReturn(true);
        $productMock->expects($this->at(2))->method('getData')->willReturn(true);
        $productMock->expects($this->at(3))->method('getData')->willReturn([1 => $usedAttributeMock]);
        $usedAttributeMock->expects($this->once())->method('getProductAttribute')->willReturn($attributeMock);
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
     * @covers \Magento\ConfigurableProduct\Model\Product\Type\Configurable::checkProductBuyState()
     */
    public function testCheckProductBuyState()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getSkipCheckRequiredOption', 'getCustomOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())->method('getSkipCheckRequiredOption')->willReturn(true);
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('info_buyRequest')
            ->willReturn($optionMock);
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
     * @covers \Magento\ConfigurableProduct\Model\Product\Type\Configurable::checkProductBuyState()
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage You need to choose options for your item.
     */
    public function testCheckProductBuyStateException()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getSkipCheckRequiredOption', 'getCustomOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())->method('getSkipCheckRequiredOption')->willReturn(true);
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('info_buyRequest')
            ->willReturn($optionMock);
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

    public function testGetProductByAttributesReturnUsedProduct()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $firstItemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $usedProductMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eavAttributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productCollectionFactory->expects($this->once())->method('create')->willReturn($productCollection);
        $productCollection->expects($this->once())->method('setProductFilter')->willReturnSelf();
        $productCollection->expects($this->once())->method('setFlag')->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToSelect')->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToFilter')->willReturnSelf();
        $productCollection->expects($this->once())->method('getFirstItem')->willReturn($firstItemMock);
        $productCollection->expects($this->once())->method('getIterator')->willReturn(
            new \ArrayIterator([$usedProductMock])
        );

        $firstItemMock->expects($this->once())->method('getId')->willReturn(false);
        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('_cache_instance_store_filter')
            ->willReturn('some_filter');
        $productMock->expects($this->any())->method('hasData')->willReturn(true);

        $eavAttributeMock->expects($this->once())->method('getAttributeCode')->willReturn('attr_code');
        $usedProductMock->expects($this->once())
            ->method('getData')->with('attr_code')
            ->willReturn($this->attributeData[1]);
        $this->eavConfig->expects($this->any())->method('getAttribute')->willReturn($eavAttributeMock);

        $this->assertEquals(
            $usedProductMock,
            $this->model->getProductByAttributes($this->attributeData, $productMock)
        );
    }

    public function testGetProductByAttributesReturnFirstItem()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $firstItemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productCollectionFactory->expects($this->any())->method('create')->willReturn($productCollection);
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

    public function testSetImageFromChildProduct()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['hasData', 'getData', 'setImage'])
            ->disableOriginalConstructor()
            ->getMock();
        $childProductMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityMetadata->expects($this->any())
            ->method('getLinkField')
            ->willReturn('link');
        $productMock->expects($this->any())->method('hasData')
            ->withConsecutive(['_cache_instance_products'])
            ->willReturnOnConsecutiveCalls(true);

        $productMock->expects($this->any())->method('getData')
            ->withConsecutive(['image'], ['image'], ['_cache_instance_products'])
            ->willReturnOnConsecutiveCalls('no_selection', 'no_selection', [$childProductMock]);

        $childProductMock->expects($this->any())->method('getData')->with('image')->willReturn('image_data');
        $productMock->expects($this->once())->method('setImage')->with('image_data')->willReturnSelf();

        $this->model->setImageFromChildProduct($productMock);
    }
}

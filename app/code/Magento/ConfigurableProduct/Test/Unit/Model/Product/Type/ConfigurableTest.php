<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Type;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Config;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Customer\Model\Session;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\CollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection as ProductCollection;

/**
 * Class \Magento\ConfigurableProduct\Test\Unit\Model\Product\Type\ConfigurableTest
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
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
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configurableAttributeFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_typeConfigurableFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_attributeCollectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * @var JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessorMock;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPool;

    /**
     * @var EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityMetadata;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $cache;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $serializer;

    /**
     * @var Config
     */
    protected $catalogConfig;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
        $this->_typeConfigurableFactory = $this->getMockBuilder(ConfigurableFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'saveProducts'])
            ->getMock();
        $this->_configurableAttributeFactoryMock = $this->getMockBuilder(AttributeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->_productCollectionFactory = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->_attributeCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
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
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->entityMetadata);

        $this->_model = $this->_objectHelper->getObject(
            Configurable::class,
            [
                'eavConfig' => $this->eavConfig,
                'typeConfigurableFactory' => $this->_typeConfigurableFactory,
                'configurableAttributeFactory' => $this->_configurableAttributeFactoryMock,
                'productCollectionFactory' => $this->_productCollectionFactory,
                'attributeCollectionFactory' => $this->_attributeCollectionFactory,
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
            ]
        );
        $refClass = new \ReflectionClass(Configurable::class);
        $refProperty = $refClass->getProperty('metadataPool');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->_model, $this->metadataPool);
    }

    public function testHasWeightTrue()
    {
        $this->assertTrue($this->_model->hasWeight(), 'This product has not weight, but it should');
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
            ->setMethods([
                'getConfigurableProductOptions',
                'getConfigurableProductLinks'
            ])
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
        $attribute = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['addData', 'setStoreId', 'setProductId', 'save', '__wakeup', '__sleep'])
            ->getMock();
        $expectedAttributeData = $this->attributeData[1];
        unset($expectedAttributeData['id']);
        $attribute->expects($this->once())->method('addData')->with($expectedAttributeData)->willReturnSelf();
        $attribute->expects($this->once())->method('setStoreId')->with(1)->willReturnSelf();
        $attribute->expects($this->once())->method('setProductId')->with(1)->willReturnSelf();
        $attribute->expects($this->once())->method('save')->willReturnSelf();

        $this->_configurableAttributeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($attribute);
        $attributeCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_attributeCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($attributeCollection);
        $this->_typeConfigurableFactory->expects($this->once())
            ->method('create')
            ->willReturnSelf();
        $this->_typeConfigurableFactory->expects($this->once())
            ->method('saveProducts')
            ->willReturnSelf();

        $this->_model->save($product);
    }

    public function testGetRelationInfo()
    {
        $info = $this->_model->getRelationInfo();
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

        $this->assertTrue($this->_model->canUseAttribute($attribute));
    }

    public function testGetUsedProducts()
    {
        $productCollectionItemData = ['array'];

        $productCollectionItem = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productCollectionItem->expects($this->once())->method('getData')->willReturn($productCollectionItemData);
        $attributeCollection->expects($this->any())->method('setProductFilter')->willReturnSelf();
        $product->expects($this->atLeastOnce())->method('getStoreId')->willReturn(5);
        $product->expects($this->once())->method('getIdentities')->willReturn(['123']);

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

        $productCollection->expects($this->atLeastOnce())->method('addAttributeToSelect')->willReturnSelf();
        $productCollection->expects($this->once())->method('setProductFilter')->willReturnSelf();
        $productCollection->expects($this->atLeastOnce())->method('setFlag')->willReturnSelf();
        $productCollection->expects($this->once())->method('addTierPriceData')->willReturnSelf();
        $productCollection->expects($this->once())->method('addFilterByRequiredOptions')->willReturnSelf();
        $productCollection->expects($this->once())->method('setStoreId')->with(5)->willReturn([]);
        $productCollection->expects($this->once())->method('getItems')->willReturn([$productCollectionItem]);

        $this->serializer->expects($this->once())->method('unserialize')->willReturn([]);
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with([$productCollectionItemData])
            ->willReturn('result');

        $this->_productCollectionFactory->expects($this->any())->method('create')->willReturn($productCollection);
        $this->_model->getUsedProducts($product);
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

        $attribute = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class)
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

        $result = $this->_model->getConfigurableAttributesAsArray($product);
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

    public function testGetConfigurableAttributes()
    {
        $configurableAttributes = '_cache_instance_configurable_attributes';

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getData', 'hasData', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();

        $product->expects($this->once())->method('hasData')->with($configurableAttributes)->willReturn(false);

        $attributeCollection = $this->getMockBuilder(Collection::class)
            ->setMethods(['setProductFilter', 'orderByPosition', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeCollection->expects($this->once())->method('setProductFilter')->willReturnSelf();
        $attributeCollection->expects($this->once())->method('orderByPosition')->willReturnSelf();
        $attributeCollection->expects($this->once())->method('load')->willReturnSelf();

        $this->_attributeCollectionFactory->expects($this->once())->method('create')->willReturn($attributeCollection);

        $product->expects($this->once())
            ->method('setData')
            ->with($configurableAttributes, $attributeCollection)
            ->willReturnSelf();

        $product->expects($this->once())
            ->method('getData')
            ->with($configurableAttributes)
            ->willReturn($attributeCollection);

        $this->assertEquals($attributeCollection, $this->_model->getConfigurableAttributes($product));
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

        $this->assertEquals($this->_model, $this->_model->resetConfigurableAttributes($product));
    }

    public function testHasOptions()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getOptions'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())->method('getOptions')->willReturn([true]);

        $this->assertTrue($this->_model->hasOptions($productMock));
    }

    public function testHasOptionsConfigurableAttribute()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getAttributeCode', 'getOptions', 'hasData', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())->method('getOptions')->willReturn([]);
        $productMock->expects($this->once())
            ->method('hasData')
            ->with('_cache_instance_configurable_attributes')->willReturn(1);
        $productMock->expects($this->once())
            ->method('getData')
            ->with('_cache_instance_configurable_attributes')->willReturn([$attributeMock]);

        $this->assertTrue($this->_model->hasOptions($productMock));
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

        $this->assertFalse($this->_model->hasOptions($productMock));
    }

    public function testIsSalable()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getStatus', 'hasData', 'getData', 'getStoreId', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $childProductMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'isSalable'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())->method('getStatus')->willReturn(1);
        $productMock->expects($this->any())->method('hasData')->willReturn(true);
        $productMock->expects($this->at(2))->method('getData')->with('is_salable')->willReturn(true);
        $productMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $productMock->expects($this->once())->method('setData')->willReturnSelf();
        $productMock->expects($this->at(6))->method('getData')->willReturn([$childProductMock]);
        $childProductMock->expects($this->once())->method('isSalable')->willReturn(true);

        $this->assertTrue($this->_model->isSalable($productMock));
    }

    public function testGetSelectedAttributesInfo()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock = $this->getMockBuilder(OptionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $usedAttributeMock = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class
        )
            ->setMethods(['getProductAttribute'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $optionMock->expects($this->once())->method('getValue')->willReturn(serialize($this->attributeData));
        $productMock->expects($this->once())->method('getCustomOption')->with('attributes')->willReturn($optionMock);
        $productMock->expects($this->once())->method('hasData')->willReturn(true);
        $productMock->expects($this->at(2))->method('getData')->willReturn(true);
        $productMock->expects($this->at(3))->method('getData')->willReturn([1 => $usedAttributeMock]);
        $usedAttributeMock->expects($this->once())->method('getProductAttribute')->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('getStoreLabel')->willReturn('attr_store_label');
        $attributeMock->expects($this->once())->method('getSourceModel')->willReturn(false);

        $this->assertEquals(
            $this->_model->getSelectedAttributesInfo($productMock),
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

    public function testCheckProductBuyState()
    {
        $this->markTestIncomplete('checkProductBuyState() method is not complete in parent class');
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
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
            ->willReturn(serialize(['super_attribute' => ['test_key' => 'test_value', 'empty_key' => '']]));

        $this->assertEquals($this->_model, $this->_model->checkProductBuyState($productMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage You need to choose options for your item.
     */
    public function testCheckProductBuyStateException()
    {
        $this->markTestIncomplete('checkProductBuyState() method is not complete in parent class');
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
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
        $optionMock->expects($this->once())->method('getValue')->willReturn(serialize([]));

        $this->_model->checkProductBuyState($productMock);
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

        $this->_productCollectionFactory->expects($this->once())->method('create')->willReturn($productCollection);
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
            $this->_model->getProductByAttributes($this->attributeData, $productMock)
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

        $this->_productCollectionFactory->expects($this->any())->method('create')->willReturn($productCollection);
        $productCollection->expects($this->once())->method('setProductFilter')->willReturnSelf();
        $productCollection->expects($this->once())->method('setFlag')->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToSelect')->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToFilter')->willReturnSelf();
        $productCollection->expects($this->once())->method('getFirstItem')->willReturn($firstItemMock);
        $firstItemMock->expects($this->once())->method('getId')->willReturn(3);
        $this->productRepository->expects($this->once())->method('getById')->with(3)->willReturn($firstItemMock);

        $this->assertEquals(
            $firstItemMock,
            $this->_model->getProductByAttributes($this->attributeData, $productMock)
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

        $productMock->expects($this->at(0))->method('getData')->with('image')->willReturn('no_selection');
        $productMock->expects($this->at(1))->method('getData')->with('image')->willReturn('no_selection');
        $productMock->expects($this->once())->method('hasData')->with('_cache_instance_products')->willReturn(true);
        $productMock->expects($this->at(3))
            ->method('getData')
            ->with('_cache_instance_products')
            ->willReturn([$childProductMock]);
        $childProductMock->expects($this->any())->method('getData')->with('image')->willReturn('image_data');
        $productMock->expects($this->once())->method('setImage')->with('image_data')->willReturnSelf();

        $this->_model->setImageFromChildProduct($productMock);
    }
}


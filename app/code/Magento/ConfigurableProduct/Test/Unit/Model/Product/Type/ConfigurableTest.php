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
use Magento\Framework\Cache\FrontendInterface;

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

    /**
     * @var Config
     */
    protected $catalogConfig;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $eventManager = $this->getMock(\Magento\Framework\Event\ManagerInterface::class, [], [], '', false);
        $fileStorageDbMock = $this->getMock(
            \Magento\MediaStorage\Helper\File\Storage\Database::class,
            [],
            [],
            '',
            false
        );
        $filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $coreRegistry = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->_typeConfigurableFactory = $this->getMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory::class,
            ['create', 'saveProducts'],
            [],
            '',
            false
        );
        $this->_configurableAttributeFactoryMock = $this->getMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_productCollectionFactory = $this->getMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_attributeCollectionFactory = $this->getMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->productRepository = $this->getMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->extensionAttributesJoinProcessorMock = $this->getMock(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class,
            [],
            [],
            '',
            false
        );
        $this->entityMetadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->entityMetadata);
        $this->cache = $this->getMockBuilder(\Magento\Framework\Cache\FrontendInterface::class)
            ->getMockForAbstractClass();
        $this->catalogConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->setMethods(['unserialize'])
            ->getMockForAbstractClass();

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
                'serializer' => $this->serializer
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
                    '__wakeup',
                    '__sleep',
                ]
            )->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())->method('dataHasChangedFor')->will($this->returnValue('false'));
        $product->expects($this->any())->method('getConfigurableAttributesData')
            ->will($this->returnValue($this->attributeData));
        $product->expects($this->once())->method('getIsDuplicate')->will($this->returnValue(true));
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue(1));
        $product->expects($this->any())->method('getAssociatedProductIds')->will($this->returnValue([2]));
        $product->expects($this->any())->method('hasData')->with('_cache_instance_used_product_attribute_ids')
            ->will($this->returnValue(true));
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
        $product->expects($this->any())
            ->method('getData')
            ->willReturnMap($dataMap);
        $attribute = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['addData', 'setStoreId', 'setProductId', 'save', '__wakeup', '__sleep'])
            ->getMock();
        $expectedAttributeData = $this->attributeData[1];
        unset($expectedAttributeData['id']);
        $attribute->expects($this->once())->method('addData')->with($expectedAttributeData)->will($this->returnSelf());
        $attribute->expects($this->once())->method('setStoreId')->with(1)->will($this->returnSelf());
        $attribute->expects($this->once())->method('setProductId')->with(1)->will($this->returnSelf());
        $attribute->expects($this->once())->method('save')->will($this->returnSelf());

        $this->_configurableAttributeFactoryMock->expects($this->any())->method('create')
            ->will($this->returnValue($attribute));

        $attributeCollection = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection::class
        )->setMethods(['setProductFilter', 'addFieldToFilter', 'walk'])->disableOriginalConstructor()
            ->getMock();
        $this->_attributeCollectionFactory->expects($this->any())->method('create')
            ->will($this->returnValue($attributeCollection));

        $this->_typeConfigurableFactory->expects($this->once())->method('create')->will($this->returnSelf());
        $this->_typeConfigurableFactory->expects($this->once())->method('saveProducts')->withAnyParameters()
            ->will($this->returnSelf());

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
        $attribute = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            [
                'getIsGlobal',
                'getIsVisible',
                'usesSource',
                'getIsUserDefined',
                '__wakeup',
                '__sleep'
            ],
            [],
            '',
            false
        );
        $attribute->expects($this->once())
            ->method('getIsGlobal')
            ->will($this->returnValue(1));
        $attribute->expects($this->once())
            ->method('getIsVisible')
            ->will($this->returnValue(1));
        $attribute->expects($this->once())
            ->method('usesSource')
            ->will($this->returnValue(1));
        $attribute->expects($this->once())
            ->method('getIsUserDefined')
            ->will($this->returnValue(1));

        $this->assertTrue($this->_model->canUseAttribute($attribute));
    }

    public function testGetUsedProducts()
    {
        $attributeCollection = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection::class
        )->setMethods(['setProductFilter', 'addFieldToFilter', 'walk'])->disableOriginalConstructor()
            ->getMock();
        $attributeCollection->expects($this->any())->method('setProductFilter')->will($this->returnSelf());
        $this->_attributeCollectionFactory->expects($this->any())->method('create')
            ->will($this->returnValue($attributeCollection));
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'dataHasChangedFor',
                    'getConfigurableAttributesData',
                    'getStoreId',
                    'getId',
                    'getData',
                    'hasData',
                    'getAssociatedProductIds',
                    'getIdentities',
                    '__wakeup',
                    '__sleep',
                ]
            )->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())->method('getConfigurableAttributesData')
            ->will($this->returnValue($this->attributeData));
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue(5));
        $product->expects($this->any())->method('getId')->will($this->returnValue(1));
        $product->expects($this->any())->method('getIdentities')->willReturn(['123']);
        $product->expects($this->any())->method('getAssociatedProductIds')->will($this->returnValue([2]));

        $product->expects($this->any())->method('hasData')
            ->will(
                $this->returnValueMap(
                    [
                        ['_cache_instance_used_product_attribute_ids', 1],
                        ['_cache_instance_products', 0],
                        ['_cache_instance_configurable_attributes', 1],
                        ['_cache_instance_used_product_attributes', 1],
                    ]
                )
            );
        $product->expects($this->any())->method('getData')
            ->will($this->returnValueMap(
                [
                    ['_cache_instance_used_product_attributes', null, []],
                ]
            ));


        $productCollection = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection::class
        )->setMethods(
            [
                'setFlag',
                'setProductFilter',
                'addStoreFilter',
                'addAttributeToSelect',
                'addFilterByRequiredOptions',
                'setStoreId',
                'addTierPriceData',
                'getIterator',
                'load',
            ]
        )->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects($this->any())->method('addAttributeToSelect')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('setProductFilter')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('setFlag')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('addTierPriceData')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('addFilterByRequiredOptions')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('setStoreId')->with(5)->will($this->returnValue([]));
        $productCollection->expects($this->any())->method('getIterator')->willReturn(
            new \ArrayIterator([])
        );


        $this->_productCollectionFactory->expects($this->any())->method('create')
            ->will($this->returnValue($productCollection));
        $this->_model->getUsedProducts($product);
    }

    /**
     * @param int $productStore
     * @param int $attributeStore
     *
     * @dataProvider getConfigurableAttributesAsArrayDataProvider
     */
    public function testGetConfigurableAttributesAsArray($productStore, $attributeStore)
    {
        $attributeSource = $this->getMockForAbstractClass(
            \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class,
            [],
            '',
            false,
            true,
            true,
            ['getAllOptions']
        );
        $attributeSource->expects($this->any())->method('getAllOptions')->will($this->returnValue([]));

        $attributeFrontend = $this->getMockForAbstractClass(
            \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend::class,
            [],
            '',
            false,
            true,
            true,
            ['getLabel']
        );
        $attributeFrontend->expects($this->any())->method('getLabel')->will($this->returnValue('Label'));

        $eavAttribute = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['getFrontend', 'getSource', 'getStoreLabel', '__wakeup', 'setStoreId', '__sleep'],
            [],
            '',
            false
        );
        $eavAttribute->expects($this->any())->method('getFrontend')->will($this->returnValue($attributeFrontend));
        $eavAttribute->expects($this->any())->method('getSource')->will($this->returnValue($attributeSource));
        $eavAttribute->expects($this->any())->method('getStoreLabel')->will($this->returnValue('Store Label'));
        $eavAttribute->expects($this->any())->method('setStoreId')->with($attributeStore);

        $attribute = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductAttribute', '__wakeup', '__sleep'])
            ->getMock();
        $attribute->expects($this->any())->method('getProductAttribute')->will($this->returnValue($eavAttribute));

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getStoreId', 'getData', 'hasData', '__wakeup', '__sleep'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue($productStore));
        $product->expects($this->any())->method('hasData')
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
            [5, 5],
            [null, 0],
        ];
    }

    public function testGetConfigurableAttributes()
    {
        $expectedData = [1];
        $configurableAttributes = '_cache_instance_configurable_attributes';

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getData', 'hasData', 'setData', 'getIdentities', 'getId', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('hasData')->with($configurableAttributes)->willReturn(false);
        $product->expects($this->once())->method('getStoreId')->willReturn(0);
        $product->expects($this->any())->method('getId')->willReturn(0);
        $product->expects($this->any())->method('getIdentities')->willReturn(['123']);
        $product->expects($this->once())->method('setData')->willReturnSelf();
        $product->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap(
                [
                    [$configurableAttributes, null, $expectedData],
                    ['link', null, 1],
                ]
            );
        $product->expects($this->once())->method('getIdentities')->willReturn([1,2,3]);

        $this->entityMetadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn('link');

        $attributeCollection = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection::class
        )
            ->setMethods(['setProductFilter', 'orderByPosition', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeCollection->expects($this->any())->method('setProductFilter')->will($this->returnSelf());
        $attributeCollection->expects($this->any())->method('orderByPosition')->will($this->returnSelf());
        $this->_attributeCollectionFactory->expects($this->any())->method('create')->willReturn($attributeCollection);

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with(
                $this->isInstanceOf(\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection::class
                )
            );

        $this->assertEquals($expectedData, $this->_model->getConfigurableAttributes($product));
    }

    public function testResetConfigurableAttributes()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['unsetData', '__wakeup', '__sleep', 'getStoreId', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())->method('unsetData')
            ->with('_cache_instance_configurable_attributes')
            ->will($this->returnSelf());

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
            ->setMethods(['__wakeup', 'getCustomOption', 'hasData', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock = $this->getMockBuilder(
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $usedAttributeMock = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class
        )
            ->setMethods(['getProductAttribute'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->setMethods(['getStoreLabel', 'getSourceModel'])
            ->disableOriginalConstructor()
            ->getMock();

        $optionMock->expects($this->any())->method('getValue')->willReturn(json_encode($this->attributeData, true));
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturn(json_decode($optionMock->getValue(), true));
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

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\Type\Configurable::checkProductBuyState()
     */
    public function testCheckProductBuyState()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getCustomOption', 'getSkipCheckRequiredOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())->method('getSkipCheckRequiredOption')->willReturn(true);
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('info_buyRequest')
            ->willReturn($optionMock);
        $optionMock->expects($this->any())
            ->method('getValue')
            ->willReturn(json_encode(['super_attribute' => ['test_key' => 'test_value', 'empty_key' => '']], true));

        $serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->setMethods(['unserialize'])
            ->getMockForAbstractClass();
        $serializer->expects($this->any())
            ->method('unserialize')
            ->willReturn(json_decode($optionMock->getValue(), true));
        $this->_objectHelper->setBackwardCompatibleProperty($this->_model, 'serializer', $serializer);

        $this->assertEquals($this->_model, $this->_model->checkProductBuyState($productMock));
    }

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\Type\Configurable::checkProductBuyState()
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage You need to choose options for your item.
     */
    public function testCheckProductBuyStateException()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getCustomOption', 'getSkipCheckRequiredOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())->method('getSkipCheckRequiredOption')->willReturn(true);
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('info_buyRequest')
            ->willReturn($optionMock);
        $optionMock->expects($this->any())->method('getValue')->willReturn('{}');
        $serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->setMethods(['unserialize'])
            ->getMockForAbstractClass();
        $serializer->expects($this->any())
            ->method('unserialize')
            ->willReturn(json_decode($optionMock->getValue(), true));
        $this->_objectHelper->setBackwardCompatibleProperty($this->_model, 'serializer', $serializer);

        $this->_model->checkProductBuyState($productMock);
    }

    public function testGetProductByAttributesReturnUsedProduct()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'hasData', 'getData', 'getResource', 'getAttributeSetId'])
            ->disableOriginalConstructor()
            ->getMock();
        $firstItemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $usedProductMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $eavAttributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->setMethods(['__wakeup', 'getId', 'getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection::class
        )
            ->setMethods(
                [
                    'setFlag',
                    'setProductFilter',
                    'addStoreFilter',
                    'addAttributeToSelect',
                    'addAttributeToFilter',
                    'getFirstItem',
                    'getIterator'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->_productCollectionFactory->expects($this->any())->method('create')
            ->will($this->returnValue($productCollection));
        $productCollection->expects($this->any())->method('setProductFilter')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('setFlag')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('addAttributeToSelect')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('addAttributeToFilter')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('getFirstItem')->willReturn($firstItemMock);
        $productCollection->expects($this->any())->method('getIterator')->willReturn(
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
            ->setMethods(['__wakeup', 'hasData', 'getData', 'getResource', 'getAttributeSetId'])
            ->disableOriginalConstructor()
            ->getMock();
        $firstItemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection::class
        )
            ->setMethods(
                [
                    'setFlag',
                    'setProductFilter',
                    'addStoreFilter',
                    'addAttributeToSelect',
                    'addAttributeToFilter',
                    'getFirstItem',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->_productCollectionFactory->expects($this->any())->method('create')
            ->will($this->returnValue($productCollection));
        $productCollection->expects($this->any())->method('setProductFilter')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('setFlag')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('addAttributeToSelect')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('addAttributeToFilter')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('getFirstItem')->willReturn($firstItemMock);
        $firstItemMock->expects(static::once())
            ->method('getId')
            ->willReturn(3);
        $this->productRepository->expects($this->once())->method('getById')->with(3)->willReturn($firstItemMock);


        $this->assertEquals(
            $firstItemMock,
            $this->_model->getProductByAttributes($this->attributeData, $productMock)
        );
    }

    public function testSetImageFromChildProduct()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'hasData', 'getData', 'setImage'])
            ->disableOriginalConstructor()
            ->getMock();
        $childProductMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getData'])
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


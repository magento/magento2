<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Type;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class \Magento\ConfigurableProduct\Test\Unit\Model\Product\Type\ConfigurableTest
 *
 * @SuppressWarnings(PHPMD.LongVariable)
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $_attributeSetFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\EntityFactory
     */
    protected $_entityFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $_stockConfiguration;

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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $this->jsonHelperMock = $this->getMock(
            'Magento\Framework\Json\Helper\Data',
            ['jsonDecode'],
            [],
            '',
            false
        );
        $fileStorageDbMock = $this->getMock('Magento\MediaStorage\Helper\File\Storage\Database', [], [], '', false);
        $filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $coreRegistry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->_productFactoryMock = $this->getMock(
            'Magento\Catalog\Model\ProductFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_typeConfigurableFactory = $this->getMock(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\ConfigurableFactory',
            ['create', 'saveProducts'],
            [],
            '',
            false
        );
        $this->_entityFactoryMock = $this->getMock(
            'Magento\Eav\Model\EntityFactory',
            ['create'],
            [],
            '',
            false
        );
        $attributeFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\AttributeFactory',
            [],
            [],
            '',
            false
        );
        $this->_configurableAttributeFactoryMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_productCollectionFactory = $this->getMock(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Product\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_attributeCollectionFactory = $this->getMock(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_attributeSetFactory = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\SetFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_stockConfiguration = $this->getMock(
            'Magento\CatalogInventory\Api\StockConfigurationInterface',
            [],
            [],
            '',
            false
        );
        $this->productRepository = $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->extensionAttributesJoinProcessorMock = $this->getMock(
            'Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface',
            [],
            [],
            '',
            false
        );

        $this->_model = $this->_objectHelper->getObject(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            [
                'productFactory' => $this->_productFactoryMock,
                'typeConfigurableFactory' => $this->_typeConfigurableFactory,
                'entityFactory' => $this->_entityFactoryMock,
                'attributeSetFactory' => $this->_attributeSetFactory,
                'eavAttributeFactory' => $attributeFactoryMock,
                'configurableAttributeFactory' => $this->_configurableAttributeFactoryMock,
                'productCollectionFactory' => $this->_productCollectionFactory,
                'attributeCollectionFactory' => $this->_attributeCollectionFactory,
                'eventManager' => $eventManager,
                'jsonHelper' => $this->jsonHelperMock,
                'fileStorageDb' => $fileStorageDbMock,
                'filesystem' => $filesystem,
                'coreRegistry' => $coreRegistry,
                'logger' => $logger,
                'stockConfiguration' => $this->_stockConfiguration,
                'productRepository' => $this->productRepository,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
            ]
        );
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
        $product = $this->getMockBuilder('\Magento\Catalog\Model\Product')
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
        $product->expects($this->any())->method('getId')->will($this->returnValue(1));
        $product->expects($this->any())->method('getAssociatedProductIds')->will($this->returnValue([2]));
        $product->expects($this->any())->method('hasData')->with('_cache_instance_used_product_attribute_ids')
            ->will($this->returnValue(true));
        $product->expects($this->any())->method('getData')->with('_cache_instance_used_product_attribute_ids')
            ->will($this->returnValue([1]));

        $attribute = $this->getMockBuilder('\Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute')
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
            '\Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Collection'
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
        $this->assertInstanceOf('Magento\Framework\Object', $info);
        $this->assertEquals('catalog_product_super_link', $info->getData('table'));
        $this->assertEquals('parent_id', $info->getData('parent_field_name'));
        $this->assertEquals('product_id', $info->getData('child_field_name'));
    }

    public function testCanUseAttribute()
    {
        $attribute = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
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
            '\Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Collection'
        )->setMethods(['setProductFilter', 'addFieldToFilter', 'walk'])->disableOriginalConstructor()
            ->getMock();
        $attributeCollection->expects($this->any())->method('setProductFilter')->will($this->returnSelf());
        $this->_attributeCollectionFactory->expects($this->any())->method('create')
            ->will($this->returnValue($attributeCollection));
        $product = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(
                [
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
        $product->expects($this->any())->method('getConfigurableAttributesData')
            ->will($this->returnValue($this->attributeData));
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue(5));
        $product->expects($this->any())->method('getId')->will($this->returnValue(1));
        $product->expects($this->any())->method('getAssociatedProductIds')->will($this->returnValue([2]));
        $product->expects($this->any())->method('hasData')
            ->will(
                $this->returnValueMap(
                    [
                        ['_cache_instance_used_product_attribute_ids', 1],
                        ['_cache_instance_products', 0],
                        ['_cache_instance_configurable_attributes', 1],
                    ]
                )
            );
        $product->expects($this->any())->method('getData')
            ->will($this->returnValue(1));
        $productCollection = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Product\Collection'
        )->setMethods(
            [
                'setFlag',
                'setProductFilter',
                'addStoreFilter',
                'addAttributeToSelect',
                'addFilterByRequiredOptions',
                'setStoreId',
            ]
        )->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects($this->any())->method('addAttributeToSelect')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('setProductFilter')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('setFlag')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('addFilterByRequiredOptions')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('setStoreId')->with(5)->will($this->returnValue([]));
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
            'Magento\Eav\Model\Entity\Attribute\Source\AbstractSource',
            [],
            '',
            false,
            true,
            true,
            ['getAllOptions']
        );
        $attributeSource->expects($this->any())->method('getAllOptions')->will($this->returnValue([]));

        $attributeFrontend = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend',
            [],
            '',
            false,
            true,
            true,
            ['getLabel']
        );
        $attributeFrontend->expects($this->any())->method('getLabel')->will($this->returnValue('Label'));

        $eavAttribute = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
            ['getFrontend', 'getSource', 'getStoreLabel', '__wakeup', 'setStoreId', '__sleep'],
            [],
            '',
            false
        );
        $eavAttribute->expects($this->any())->method('getFrontend')->will($this->returnValue($attributeFrontend));
        $eavAttribute->expects($this->any())->method('getSource')->will($this->returnValue($attributeSource));
        $eavAttribute->expects($this->any())->method('getStoreLabel')->will($this->returnValue('Store Label'));
        $eavAttribute->expects($this->any())->method('setStoreId')->with($attributeStore);

        $attribute = $this->getMockBuilder('\Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['getProductAttribute', '__wakeup', '__sleep'])
            ->getMock();
        $attribute->expects($this->any())->method('getProductAttribute')->will($this->returnValue($eavAttribute));

        $product = $this->getMockBuilder('\Magento\Catalog\Model\Product')
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
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getData', 'hasData', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('hasData')->with($configurableAttributes)->willReturn(false);
        $product->expects($this->once())->method('setData')->willReturnSelf();
        $product->expects($this->once())->method('getData')->with($configurableAttributes)->willReturn($expectedData);

        $attributeCollection = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Collection'
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
                $this->isInstanceOf(
                    'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Collection'
                )
            );

        $this->assertEquals($expectedData, $this->_model->getConfigurableAttributes($product));
    }

    public function testResetConfigurableAttributes()
    {
        $product = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['unsetData', '__wakeup', '__sleep'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())->method('unsetData')
            ->with('_cache_instance_configurable_attributes')
            ->will($this->returnSelf());

        $this->assertEquals($this->_model, $this->_model->resetConfigurableAttributes($product));
    }

    public function testHasOptions()
    {
        $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getOptions'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())->method('getOptions')->willReturn([true]);

        $this->assertTrue($this->_model->hasOptions($productMock));
    }

    public function testHasOptionsConfigurableAttribute()
    {
        $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getAttributeCode', 'getOptions', 'hasData', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock = $this->getMockBuilder('\Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())->method('getOptions')->willReturn([]);
        $productMock->expects($this->once())
            ->method('hasData')
            ->with('_cache_instance_configurable_attributes')->willReturn(1);
        $productMock->expects($this->once())
            ->method('getData')
            ->with('_cache_instance_configurable_attributes')->willReturn([$attributeMock]);
        $attributeMock->expects($this->once())->method('getData')->with('options')->willReturn(5);

        $this->assertTrue($this->_model->hasOptions($productMock));
    }

    public function testHasOptionsFalse()
    {
        $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
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
        $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getStatus', 'hasData', 'getData', 'getStoreId', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $childProductMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
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
        $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getCustomOption', 'hasData', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $usedAttributeMock = $this->getMockBuilder(
            '\Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute'
        )
            ->setMethods(['getProductAttribute'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock = $this->getMockBuilder('\Magento\Catalog\Model\Resource\Eav\Attribute')
            ->setMethods(['getStoreLabel', 'getSourceModel'])
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
            [['label' => 'attr_store_label', 'value' => '']]
        );
    }

    public function testCheckProductBuyState()
    {
        $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getCustomOption', 'getSkipCheckRequiredOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Item\Option')
            ->setMethods(['getValue'])
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
        $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getCustomOption', 'getSkipCheckRequiredOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Item\Option')
            ->setMethods(['getValue'])
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
        $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'hasData', 'getData', 'getResource', 'getAttributeSetId'])
            ->disableOriginalConstructor()
            ->getMock();
        $firstItemMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $usedProductMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $eavAttributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->setMethods(['getId', 'getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Product\Collection'
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
        $firstItemMock->expects($this->once())->method('getId')->willReturn(false);
        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('_cache_instance_store_filter')
            ->willReturn('some_filter');
        $productMock->expects($this->any())->method('hasData')->willReturn(true);
        $productMock->expects($this->at(3))->method('getData')
            ->with('_cache_instance_products')
            ->willReturn([$usedProductMock]);
        $productMock->expects($this->at(5))->method('getData')
            ->with('_cache_instance_product_set_attributes')
            ->willReturn([$eavAttributeMock]);
        $eavAttributeMock->expects($this->once())->method('getId')->willReturn(1);
        $eavAttributeMock->expects($this->once())->method('getAttributeCode')->willReturn('attr_code');
        $usedProductMock->expects($this->once())
            ->method('getData')->with('attr_code')
            ->willReturn($this->attributeData[1]);

        $this->assertEquals(
            $usedProductMock,
            $this->_model->getProductByAttributes($this->attributeData, $productMock)
        );
    }

    public function testGetProductByAttributesReturnFirstItem()
    {
        $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'hasData', 'getData', 'getResource', 'getAttributeSetId'])
            ->disableOriginalConstructor()
            ->getMock();
        $firstItemMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Product\Collection'
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
        $firstItemMock->expects($this->any())->method('getId')->willReturn(3);
        $this->productRepository->expects($this->once())->method('getById')->with(3)->willReturn($firstItemMock);

        $this->assertEquals(
            $firstItemMock,
            $this->_model->getProductByAttributes($this->attributeData, $productMock)
        );
    }

    public function testSetImageFromChildProduct()
    {
        $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'hasData', 'getData', 'setImage'])
            ->disableOriginalConstructor()
            ->getMock();
        $childProductMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
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

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGenerateSimpleProducts()
    {
        $productsData = [
            6 =>
                [
                    'image' => 'image.jpg',
                    'name' => 'config-red',
                    'configurable_attribute' => '{"new_attr":"6"}',
                    'sku' => 'config-red',
                    'quantity_and_stock_status' =>
                        [
                            'qty' => '',
                        ],
                    'weight' => '333',
                ]
        ];
        $stockData = [
            'manage_stock' => '0',
            'use_config_enable_qty_increments' => '1',
            'use_config_qty_increments' => '1',
            'use_config_manage_stock' => 0,
            'is_decimal_divided' => 0
        ];

        $parentProductMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(
                [
                    '__wakeup',
                    'hasData',
                    'getData',
                    'getNewVariationsAttributeSetId',
                    'getStockData',
                    'getQuantityAndStockStatus',
                    'getWebsiteIds'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $newSimpleProductMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(
                [
                    '__wakeup',
                    'save',
                    'getId',
                    'setStoreId',
                    'setTypeId',
                    'setAttributeSetId',
                    'getTypeInstance',
                    'getStoreId',
                    'addData',
                    'setWebsiteIds',
                    'setStatus',
                    'setVisibility'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute')
            ->setMethods(['isInSet', 'setAttributeSetId', 'setAttributeGroupId', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeSetMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Set')
            ->setMethods(['load', 'addSetInfo', 'getDefaultGroupId'])
            ->disableOriginalConstructor()
            ->getMock();
        $eavEntityMock = $this->getMockBuilder('\Magento\Eav\Model\Entity')
            ->setMethods(['setType', 'getTypeId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Type')
            ->setMethods(['getEditableAttributes'])
            ->disableOriginalConstructor()
            ->getMock();
        $editableAttributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->setMethods(['getIsUnique', 'getAttributeCode', 'getFrontend', 'getIsVisible'])
            ->disableOriginalConstructor()
            ->getMock();
        $frontendAttributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Frontend')
            ->setMethods(['getInputType'])
            ->disableOriginalConstructor()
            ->getMock();

        $parentProductMock->expects($this->once())
            ->method('hasData')
            ->with('_cache_instance_used_product_attributes')
            ->willReturn(true);
        $parentProductMock->expects($this->once())
            ->method('getData')
            ->with('_cache_instance_used_product_attributes')
            ->willReturn([$attributeMock]);
        $parentProductMock->expects($this->any())
            ->method('getNewVariationsAttributeSetId')
            ->willReturn('new_attr_set_id');
        $this->_attributeSetFactory->expects($this->once())->method('create')->willReturn($attributeSetMock);
        $attributeSetMock->expects($this->once())->method('load')->with('new_attr_set_id')->willReturnSelf();
        $this->_entityFactoryMock->expects($this->once())->method('create')->willReturn($eavEntityMock);
        $eavEntityMock->expects($this->once())->method('setType')->with('catalog_product')->willReturnSelf();
        $eavEntityMock->expects($this->once())->method('getTypeId')->willReturn('type_id');
        $attributeSetMock->expects($this->once())->method('addSetInfo')->with('type_id', [$attributeMock]);
        $attributeMock->expects($this->once())->method('isInSet')->with('new_attr_set_id')->willReturn(false);
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with('new_attr_set_id')->willReturnSelf();
        $attributeSetMock->expects($this->once())
            ->method('getDefaultGroupId')
            ->with('new_attr_set_id')
            ->willReturn('default_group_id');
        $attributeMock->expects($this->once())
            ->method('setAttributeGroupId')
            ->with('default_group_id')
            ->willReturnSelf();
        $attributeMock->expects($this->once())->method('save')->willReturnSelf();
        $this->_productFactoryMock->expects($this->once())->method('create')->willReturn($newSimpleProductMock);
        $this->jsonHelperMock->expects($this->once())
            ->method('jsonDecode')
            ->with('{"new_attr":"6"}')
            ->willReturn(['new_attr' => 6]);
        $newSimpleProductMock->expects($this->once())->method('setStoreId')->with(0)->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('setTypeId')->with('simple')->willReturnSelf();
        $newSimpleProductMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with('new_attr_set_id')
            ->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('getTypeInstance')->willReturn($productTypeMock);
        $productTypeMock->expects($this->once())
            ->method('getEditableAttributes')
            ->with($newSimpleProductMock)
            ->willReturn([$editableAttributeMock]);
        $editableAttributeMock->expects($this->once())->method('getIsUnique')->willReturn(false);
        $editableAttributeMock->expects($this->once())->method('getAttributeCode')->willReturn('some_code');
        $editableAttributeMock->expects($this->any())->method('getFrontend')->willReturn($frontendAttributeMock);
        $frontendAttributeMock->expects($this->any())->method('getInputType')->willReturn('input_type');
        $editableAttributeMock->expects($this->any())->method('getIsVisible')->willReturn(false);
        $parentProductMock->expects($this->once())->method('getStockData')->willReturn($stockData);
        $parentProductMock->expects($this->once())
            ->method('getQuantityAndStockStatus')
            ->willReturn(['is_in_stock' => 1]);
        $newSimpleProductMock->expects($this->once())->method('getStoreId')->willReturn('store_id');
        $this->_stockConfiguration->expects($this->once())
            ->method('getManageStock')
            ->with('store_id')
            ->willReturn(1);
        $newSimpleProductMock->expects($this->once())->method('addData')->willReturnSelf();
        $parentProductMock->expects($this->once())->method('getWebsiteIds')->willReturn('website_id');
        $newSimpleProductMock->expects($this->once())->method('setWebsiteIds')->with('website_id')->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('setVisibility')->with(1)->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('save')->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('getId')->willReturn('product_id');

        $this->assertEquals(['product_id'], $this->_model->generateSimpleProducts($parentProductMock, $productsData));
    }
}


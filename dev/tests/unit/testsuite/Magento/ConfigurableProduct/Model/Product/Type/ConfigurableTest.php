<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type;

/**
 * Class \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableTest
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
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
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectHelper;

    protected function setUp()
    {
        $this->_objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $coreDataMock = $this->getMock('Magento\Core\Helper\Data', [], [], '', false);
        $fileStorageDbMock = $this->getMock('Magento\Core\Helper\File\Storage\Database', [], [], '', false);
        $filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $coreRegistry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $productFactoryMock = $this->getMock('Magento\Catalog\Model\ProductFactory', [], [], '', false);
        $this->_typeConfigurableFactory = $this->getMock(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\ConfigurableFactory',
            ['create', 'saveProducts'],
            [],
            '',
            false
        );
        $entityFactoryMock = $this->getMock('Magento\Eav\Model\EntityFactory', [], [], '', false);
        $setFactoryMock = $this->getMock('Magento\Eav\Model\Entity\Attribute\SetFactory', [], [], '', false);
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
        $this->_model = $this->_objectHelper->getObject(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            [
                'productFactory' => $productFactoryMock,
                'typeConfigurableFactory' => $this->_typeConfigurableFactory,
                'entityFactory' => $entityFactoryMock,
                'attributeSetFactory' => $setFactoryMock,
                'eavAttributeFactory' => $attributeFactoryMock,
                'configurableAttributeFactory' => $this->_configurableAttributeFactoryMock,
                'productCollectionFactory' => $this->_productCollectionFactory,
                'attributeCollectionFactory' => $this->_attributeCollectionFactory,
                'eventManager' => $eventManager,
                'coreData' => $coreDataMock,
                'fileStorageDb' => $fileStorageDbMock,
                'filesystem' => $filesystem,
                'coreRegistry' => $coreRegistry,
                'logger' => $logger
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
        $attributeData = [1 => [
            'id' => 1,
            'code' => 'someattr',
            'attribute_id' => 111,
            'position' => 0,
            'label' => 'Some Super Attribute',
            'values' => [],
        ]];

        $product = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['getIsDuplicate', 'dataHasChangedFor', 'getConfigurableAttributesData', 'getStoreId',
                'getId', 'getData', 'hasData', 'getAssociatedProductIds', '__wakeup', '__sleep',
            ])->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())->method('dataHasChangedFor')->will($this->returnValue('false'));
        $product->expects($this->any())->method('getConfigurableAttributesData')
            ->will($this->returnValue($attributeData));
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
        $expectedAttributeData = $attributeData[1];
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
                'getIsConfigurable',
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
            ->method('getIsConfigurable')
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
            ->setMethods(['dataHasChangedFor', 'getConfigurableAttributesData', 'getStoreId',
                          'getId', 'getData', 'hasData', 'getAssociatedProductIds', '__wakeup', '__sleep',
            ])->disableOriginalConstructor()
            ->getMock();
        $attributeData = [1 => [
            'id' => 1,
            'code' => 'someattr',
            'attribute_id' => 111,
            'position' => 0,
            'label' => 'Some Super Attribute',
            'values' => [],
        ]];
        $product->expects($this->any())->method('getConfigurableAttributesData')
            ->will($this->returnValue($attributeData));
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue(5));
        $product->expects($this->any())->method('getId')->will($this->returnValue(1));
        $product->expects($this->any())->method('getAssociatedProductIds')->will($this->returnValue([2]));
        $product->expects($this->any())->method('hasData')
            ->will($this->returnValueMap([
                ['_cache_instance_used_product_attribute_ids', 1],
                ['_cache_instance_products', 0],
                ['_cache_instance_configurable_attributes', 1],
            ]));
        $product->expects($this->any())->method('getData')
            ->will($this->returnValue(1));
        $productCollection = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Product\Collection'
        )->setMethods(
            ['setFlag', 'setProductFilter', 'addStoreFilter', 'addAttributeToSelect', 'addFilterByRequiredOptions',
             'setStoreId', ]
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
            ->will($this->returnValueMap([
                ['_cache_instance_configurable_attributes', 1],
            ]));
        $product->expects($this->any())->method('getData')
            ->will($this->returnValueMap([
                ['_cache_instance_configurable_attributes', null, [$attribute]],
            ]));

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
}

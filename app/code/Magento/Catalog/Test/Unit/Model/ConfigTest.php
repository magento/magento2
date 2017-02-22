<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Catalog\Model\Config::loadAttributeSets
     * @return object
     */
    public function testLoadAttributeSets()
    {
        $setCollectionFactory = $this->getMock(
            '\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(
            'Magento\Catalog\Model\Config',
            ['setCollectionFactory' => $setCollectionFactory]
        );
        $setItem = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Set',
            ['getEntityTypeId', 'getAttributeSetName', '__wakeup'],
            [],
            '',
            false
        );
        $setItem->expects($this->once())->method('getEntityTypeId')->will($this->returnValue(1));
        $setItem->expects($this->once())->method('getAttributeSetName')->will($this->returnValue('name'));
        $setCollection = $this->getMock(
            '\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection',
            ['load'],
            [],
            '',
            false
        );
        $setCollection->expects($this->once())->method('load')->will($this->returnValue([1 => $setItem]));
        $setCollectionFactory->expects($this->any())->method('create')->will($this->returnValue($setCollection));
        $model->loadAttributeSets();
        return $model;
    }

    /**
     * @depends testLoadAttributeSets
     * @covers \Magento\Catalog\Model\Config::getAttributeSetName
     */
    public function testGetAttributeSetName($model)
    {
        $this->assertEquals('name', $model->getAttributeSetName(1, 1));
        $this->assertFalse($model->getAttributeSetName(2, 1));
    }
    /**
     * @depends testLoadAttributeSets
     * @covers \Magento\Catalog\Model\Config::getAttributeSetId
     */
    public function testGetAttributeSetId($model)
    {
        $this->assertEquals(1, $model->getAttributeSetId(1, 'name'));
        $this->assertFalse($model->getAttributeSetId(1, 'noname'));
    }

    /**
     * @covers \Magento\Catalog\Model\Config::loadAttributeGroups
     * @return object
     */
    public function testLoadAttributeGroups()
    {
        $groupCollectionFactory = $this->getMock(
            '\Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(
            'Magento\Catalog\Model\Config',
            ['groupCollectionFactory' => $groupCollectionFactory]
        );
        $setItem = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Group',
            ['getAttributeSetId', 'getAttributeGroupName', '__wakeup'],
            [],
            '',
            false
        );
        $setItem->expects($this->once())->method('getAttributeSetId')->will($this->returnValue(1));
        $setItem->expects($this->once())->method('getAttributeGroupName')->will($this->returnValue('name'));
        $groupCollection = $this->getMock(
            '\Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection',
            ['load'],
            [],
            '',
            false
        );
        $groupCollection->expects($this->once())->method('load')->will($this->returnValue([1 => $setItem]));
        $groupCollectionFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($groupCollection));
        $model->loadAttributeGroups();
        return $model;
    }

    /**
     * @depends testLoadAttributeGroups
     * @covers \Magento\Catalog\Model\Config::getAttributeGroupName
     */
    public function testGetAttributeGroupName($model)
    {
        $this->assertEquals('name', $model->getAttributeGroupName(1, 1));
        $this->assertFalse($model->getAttributeGroupName(2, 1));
    }
    /**
     * @depends testLoadAttributeGroups
     * @covers \Magento\Catalog\Model\Config::getAttributeGroupId
     */
    public function testGetAttributeGroupId($model)
    {
        $this->assertEquals(1, $model->getAttributeGroupId(1, 'name'));
        $this->assertFalse($model->getAttributeGroupId(1, 'noname'));
    }

    /**
     * @covers \Magento\Catalog\Model\Config::loadProductTypes
     * @return object
     */
    public function testLoadProductTypes()
    {
        $productTypeFactory = $this->getMock('\Magento\Catalog\Model\Product\TypeFactory', ['create'], [], '', false);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(
            'Magento\Catalog\Model\Config',
            ['productTypeFactory' => $productTypeFactory]
        );
        $typeCollection = $this->getMock('\Magento\Catalog\Model\Product\Type', ['getOptionArray'], [], '', false);
        $typeCollection->expects($this->once())->method('getOptionArray')->will($this->returnValue([1 => 'name']));
        $productTypeFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($typeCollection));
        $model->loadProductTypes();
        return $model;
    }

    /**
     * @depends testLoadProductTypes
     * @covers \Magento\Catalog\Model\Config::getProductTypeId
     */
    public function testGetProductTypeId($model)
    {
        $this->assertEquals(1, $model->getProductTypeId('name'));
        $this->assertFalse($model->getProductTypeId('noname'));
    }

    /**
     * @depends testLoadProductTypes
     * @covers \Magento\Catalog\Model\Config::getProductTypeName
     */
    public function testGetProductTypeName($model)
    {
        $this->assertEquals('name', $model->getProductTypeName(1));
        $this->assertFalse($model->getProductTypeName(2));
    }

    /**
     * @param $expected
     * @param $data
     * @param $search
     *
     * @covers \Magento\Catalog\Model\Config::getSourceOptionId
     * @dataProvider getSourceOptionIdDataProvider
     */
    public function testGetSourceOptionId($expected, $data, $search)
    {
        $object = $this->getMock('\Magento\Framework\DataObject', ['getAllOptions'], [], '', false);
        $object->expects($this->once())->method('getAllOptions')->will($this->returnValue($data));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject('Magento\Catalog\Model\Config');
        $this->assertEquals($expected, $model->getSourceOptionId($object, $search));
    }

    /**
     * @return array
     */
    public function getSourceOptionIdDataProvider()
    {
        return [
            [1, [['label' => 'name', 'value' => 1]], 1],
            [1, [['label' => 'name', 'value' => 1]], 'name'],
            [null, [['label' => 'name', 'value' => 1]], 2],
        ];
    }

    /**
     * @return array
     */
    protected function prepareConfigModelForAttributes()
    {
        $storeId = 1;
        $attributeData = ['attribute_code' => 1];
        $attributesData = [$attributeData];
        $entityType = 'catalog_product';
        $storeLabel = 'label';
        $attributeCode = 'code';

        $attribute = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            ['getStoreLabel', 'getAttributeCode', '__wakeup'],
            [],
            '',
            false
        );
        $attribute->expects($this->any())->method('getStoreLabel')->will($this->returnValue($storeLabel));
        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attributeCode));

        $storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $store = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue($storeId));

        $config = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Config',
            ['setStoreId', 'getAttributesUsedInListing', 'getAttributesUsedForSortBy', '__wakeup'],
            [],
            '',
            false
        );
        $config->expects($this->any())->method('setStoreId')->with($storeId)->will($this->returnSelf());
        $config->expects($this->any())->method('getAttributesUsedInListing')->will($this->returnValue($attributesData));
        $config->expects($this->any())->method('getAttributesUsedForSortBy')->will($this->returnValue($attributesData));

        $configFactory =
            $this->getMock('\Magento\Catalog\Model\ResourceModel\ConfigFactory', ['create'], [], '', false);
        $configFactory->expects($this->atLeastOnce())->method('create')->will($this->returnValue($config));

        $eavConfig = $this->getMock(
            '\Magento\Eav\Model\Config',
            ['getAttribute', 'importAttributesData'],
            [],
            '',
            false
        );
        $eavConfig->expects($this->once())->method('importAttributesData')->with($entityType, $attributesData)
            ->will($this->returnSelf());
        $eavConfig->expects($this->once())->method('getAttribute')->with($entityType, $attributeData['attribute_code'])
            ->will($this->returnValue($attribute));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(
            'Magento\Catalog\Model\Config',
            ['configFactory' => $configFactory, 'storeManager' => $storeManager, 'eavConfig' => $eavConfig]
        );

        return [$model, $attribute];
    }

    /**
     * @covers \Magento\Catalog\Model\Config::getAttributesUsedInProductListing
     * return object
     */
    public function testGetAttributesUsedInProductListing()
    {
        list($model, $attribute) = $this->prepareConfigModelForAttributes();
        $this->assertEquals([1 => $attribute], $model->getAttributesUsedInProductListing());
        return $model;
    }

    /**
     * @depends testGetAttributesUsedInProductListing
     * @covers \Magento\Catalog\Model\Config::getProductAttributes
     */
    public function testGetProductAttributes($model)
    {
        $this->assertEquals([1], $model->getProductAttributes());
    }

    /**
     * @covers \Magento\Catalog\Model\Config::getAttributesUsedForSortBy
     */
    public function testGetAttributesUsedForSortBy()
    {
        list($model, $attribute) = $this->prepareConfigModelForAttributes();
        $this->assertEquals([1 => $attribute], $model->getAttributesUsedForSortBy());
    }

    /**
     * @covers \Magento\Catalog\Model\Config::getAttributeUsedForSortByArray
     */
    public function testGetAttributeUsedForSortByArray()
    {
        list($model) = $this->prepareConfigModelForAttributes();
        $this->assertEquals(['position' => 'Position', 'code' => 'label'], $model->getAttributeUsedForSortByArray());
    }

    /**
     * @covers \Magento\Catalog\Model\Config::getProductListDefaultSortBy
     */
    public function testGetProductListDefaultSortBy()
    {
        $scopeConfig = $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );
        $scopeConfig->expects($this->once())->method('getValue')
            ->with('catalog/frontend/default_sort_by', 'store', null)->will($this->returnValue(1));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject('Magento\Catalog\Model\Config', ['scopeConfig' => $scopeConfig]);
        $this->assertEquals(1, $model->getProductListDefaultSortBy());
    }
}

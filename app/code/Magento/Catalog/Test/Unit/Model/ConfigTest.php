<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Magento\Catalog\Model\Config::loadAttributeSets
     * @return object
     */
    public function testLoadAttributeSets()
    {
        $setCollectionFactory = $this->createPartialMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory::class,
            ['create']
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(
            \Magento\Catalog\Model\Config::class,
            ['setCollectionFactory' => $setCollectionFactory]
        );
        $setItem = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\Set::class,
            ['getEntityTypeId', 'getAttributeSetName', '__wakeup']
        );
        $setItem->expects($this->once())->method('getEntityTypeId')->willReturn(1);
        $setItem->expects($this->once())->method('getAttributeSetName')->willReturn('name');
        $setCollection = $this->createPartialMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::class,
            ['load']
        );
        $setCollection->expects($this->once())->method('load')->willReturn([1 => $setItem]);
        $setCollectionFactory->expects($this->any())->method('create')->willReturn($setCollection);
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
        $groupCollectionFactory = $this->createPartialMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory::class,
            ['create']
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(
            \Magento\Catalog\Model\Config::class,
            ['groupCollectionFactory' => $groupCollectionFactory]
        );
        $setItem = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\Group::class,
            ['getAttributeSetId', 'getAttributeGroupName', '__wakeup']
        );
        $setItem->expects($this->once())->method('getAttributeSetId')->willReturn(1);
        $setItem->expects($this->once())->method('getAttributeGroupName')->willReturn('name');
        $groupCollection = $this->createPartialMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection::class,
            ['load']
        );
        $groupCollection->expects($this->once())->method('load')->willReturn([1 => $setItem]);
        $groupCollectionFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($groupCollection);
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
        $productTypeFactory = $this->createPartialMock(\Magento\Catalog\Model\Product\TypeFactory::class, ['create']);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(
            \Magento\Catalog\Model\Config::class,
            ['productTypeFactory' => $productTypeFactory]
        );
        $typeCollection = $this->createPartialMock(\Magento\Catalog\Model\Product\Type::class, ['getOptionArray']);
        $typeCollection->expects($this->once())->method('getOptionArray')->willReturn([1 => 'name']);
        $productTypeFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($typeCollection);
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
        $object = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getAllOptions']);
        $object->expects($this->once())->method('getAllOptions')->willReturn($data);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(\Magento\Catalog\Model\Config::class);
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

        $attribute = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            ['getStoreLabel', 'getAttributeCode', '__wakeup']
        );
        $attribute->expects($this->any())->method('getStoreLabel')->willReturn($storeLabel);
        $attribute->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);

        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $store->expects($this->any())->method('getId')->willReturn($storeId);

        $config = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Config::class,
            ['setStoreId', 'getAttributesUsedInListing', 'getAttributesUsedForSortBy', '__wakeup']
        );
        $config->expects($this->any())->method('setStoreId')->with($storeId)->willReturnSelf();
        $config->expects($this->any())->method('getAttributesUsedInListing')->willReturn($attributesData);
        $config->expects($this->any())->method('getAttributesUsedForSortBy')->willReturn($attributesData);

        $configFactory =
            $this->createPartialMock(\Magento\Catalog\Model\ResourceModel\ConfigFactory::class, ['create']);
        $configFactory->expects($this->atLeastOnce())->method('create')->willReturn($config);

        $eavConfig = $this->createPartialMock(
            \Magento\Eav\Model\Config::class,
            ['getAttribute', 'importAttributesData']
        );
        $eavConfig->expects($this->once())->method('importAttributesData')->with($entityType, $attributesData)
            ->willReturnSelf();
        $eavConfig->expects($this->once())->method('getAttribute')->with($entityType, $attributeData['attribute_code'])
            ->willReturn($attribute);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(
            \Magento\Catalog\Model\Config::class,
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
        $scopeConfig = $this->createPartialMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );
        $scopeConfig->expects($this->once())->method('getValue')
            ->with('catalog/frontend/default_sort_by', 'store', null)->willReturn(1);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(\Magento\Catalog\Model\Config::class, ['scopeConfig' => $scopeConfig]);
        $this->assertEquals(1, $model->getProductListDefaultSortBy());
    }
}

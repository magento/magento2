<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\TypeFactory;
use Magento\Catalog\Model\ResourceModel\ConfigFactory;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[CoversClass(\Magento\Catalog\Model\Config::class)]
class ConfigTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @return object
     */
    public function testLoadAttributeSets()
    {
        $setCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $objectManager = new ObjectManager($this);
        $model = $objectManager->getObject(
            Config::class,
            ['setCollectionFactory' => $setCollectionFactory]
        );
        $setItem = $this->createPartialMock(
            Set::class,
            ['getEntityTypeId', 'getAttributeSetName']
        );
        $setItem->expects($this->once())->method('getEntityTypeId')->willReturn(1);
        $setItem->expects($this->once())->method('getAttributeSetName')->willReturn('name');
        $setCollection = $this->createPartialMock(
            Collection::class,
            ['load']
        );
        $setCollection->expects($this->once())->method('load')->willReturn([1 => $setItem]);
        $setCollectionFactory->method('create')->willReturn($setCollection);
        $model->loadAttributeSets();
        return $model;
    }

    #[Depends('testLoadAttributeSets')]
    public function testGetAttributeSetName($model)
    {
        $this->assertEquals('name', $model->getAttributeSetName(1, 1));
        $this->assertFalse($model->getAttributeSetName(2, 1));
    }

    #[Depends('testLoadAttributeSets')]
    public function testGetAttributeSetId($model)
    {
        $this->assertEquals(1, $model->getAttributeSetId(1, 'name'));
        $this->assertFalse($model->getAttributeSetId(1, 'noname'));
    }

    /**
     * @return object
     */
    public function testLoadAttributeGroups()
    {
        $groupCollectionFactory = $this->createPartialMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory::class,
            ['create']
        );
        $objectManager = new ObjectManager($this);
        $model = $objectManager->getObject(
            Config::class,
            ['groupCollectionFactory' => $groupCollectionFactory]
        );
        $setItem = $this->createPartialMock(
            Group::class,
            ['getAttributeSetId', 'getAttributeGroupName']
        );
        $setItem->expects($this->once())->method('getAttributeSetId')->willReturn(1);
        $setItem->expects($this->once())->method('getAttributeGroupName')->willReturn('name');
        $groupCollection = $this->createPartialMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection::class,
            ['load']
        );
        $groupCollection->expects($this->once())->method('load')->willReturn([1 => $setItem]);
        $groupCollectionFactory
            ->method('create')->willReturn($groupCollection);
        $model->loadAttributeGroups();
        return $model;
    }

    #[Depends('testLoadAttributeGroups')]
    public function testGetAttributeGroupName($model)
    {
        $this->assertEquals('name', $model->getAttributeGroupName(1, 1));
        $this->assertFalse($model->getAttributeGroupName(2, 1));
    }

    #[Depends('testLoadAttributeGroups')]
    public function testGetAttributeGroupId($model)
    {
        $this->assertEquals(1, $model->getAttributeGroupId(1, 'name'));
        $this->assertFalse($model->getAttributeGroupId(1, 'noname'));
    }

    /**
     * @return object
     */
    public function testLoadProductTypes()
    {
        $productTypeFactory = $this->createPartialMock(TypeFactory::class, ['create']);
        $objectManager = new ObjectManager($this);
        $model = $objectManager->getObject(
            Config::class,
            ['productTypeFactory' => $productTypeFactory]
        );
        $typeCollection = $this->createPartialMock(Type::class, ['getOptionArray']);
        $typeCollection->expects($this->once())->method('getOptionArray')->willReturn([1 => 'name']);
        $productTypeFactory
            ->method('create')->willReturn($typeCollection);
        $model->loadProductTypes();
        return $model;
    }

    #[Depends('testLoadProductTypes')]
    public function testGetProductTypeId($model)
    {
        $this->assertEquals(1, $model->getProductTypeId('name'));
        $this->assertFalse($model->getProductTypeId('noname'));
    }

    #[Depends('testLoadProductTypes')]
    public function testGetProductTypeName($model)
    {
        $this->assertEquals('name', $model->getProductTypeName(1));
        $this->assertFalse($model->getProductTypeName(2));
    }

    /**
     * @param $expected
     * @param $data
     * @param $search
     */
    #[DataProvider('getSourceOptionIdDataProvider')]
    public function testGetSourceOptionId($expected, $data, $search)
    {
        $object = $this->createPartialMockWithReflection(DataObject::class, ['getAllOptions']);
        $object->expects($this->once())->method('getAllOptions')->willReturn($data);
        $objectManager = new ObjectManager($this);
        $model = $objectManager->getObject(Config::class);
        $this->assertEquals($expected, $model->getSourceOptionId($object, $search));
    }

    /**
     * @return array
     */
    public static function getSourceOptionIdDataProvider()
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

        $attribute = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            ['setStoreLabel', 'getStoreLabel', 'setAttributeCode', 'getAttributeCode', '_construct']
        );
        $attrState = [];
        $attribute->method('setStoreLabel')->willReturnCallback(function ($value) use (&$attrState, $attribute) {
            $attrState['store_label'] = $value;
            return $attribute;
        });
        $attribute->method('getStoreLabel')->willReturnCallback(function () use (&$attrState) {
            return $attrState['store_label'] ?? null;
        });
        $attribute->method('setAttributeCode')->willReturnCallback(function ($value) use (&$attrState, $attribute) {
            $attrState['attribute_code'] = $value;
            return $attribute;
        });
        $attribute->method('getAttributeCode')->willReturnCallback(function () use (&$attrState) {
            return $attrState['attribute_code'] ?? null;
        });
        $attribute->setStoreLabel($storeLabel);
        $attribute->setAttributeCode($attributeCode);

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $store = $this->createMock(Store::class);
        $storeManager->method('getStore')->willReturn($store);
        $store->method('getId')->willReturn($storeId);

        $config = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Config::class,
            ['setStoreId', 'getAttributesUsedInListing', 'getAttributesUsedForSortBy']
        );
        $config->expects($this->any())->method('setStoreId')->with($storeId)->willReturnSelf();
        $config->method('getAttributesUsedInListing')->willReturn($attributesData);
        $config->method('getAttributesUsedForSortBy')->willReturn($attributesData);

        $configFactory =
            $this->createPartialMock(ConfigFactory::class, ['create']);
        $configFactory->expects($this->atLeastOnce())->method('create')->willReturn($config);

        $eavConfig = $this->createPartialMock(
            \Magento\Eav\Model\Config::class,
            ['getAttribute', 'importAttributesData']
        );
        $eavConfig->expects($this->once())->method('importAttributesData')->with(
            $entityType,
            $attributesData
        )->willReturnSelf();
        $eavConfig->expects($this->once())->method('getAttribute')->with($entityType, $attributeData['attribute_code'])
            ->willReturn($attribute);

        $objectManager = new ObjectManager($this);
        $model = $objectManager->getObject(
            Config::class,
            ['configFactory' => $configFactory, 'storeManager' => $storeManager, 'eavConfig' => $eavConfig]
        );

        return [$model, $attribute];
    }

    public function testGetAttributesUsedInProductListing()
    {
        list($model, $attribute) = $this->prepareConfigModelForAttributes();
        $this->assertEquals([1 => $attribute], $model->getAttributesUsedInProductListing());
        return $model;
    }

    #[Depends('testGetAttributesUsedInProductListing')]
    public function testGetProductAttributes($model)
    {
        $this->assertEquals([1], $model->getProductAttributes());
    }

    public function testGetAttributesUsedForSortBy()
    {
        list($model, $attribute) = $this->prepareConfigModelForAttributes();
        $this->assertEquals([1 => $attribute], $model->getAttributesUsedForSortBy());
    }

    public function testGetAttributeUsedForSortByArray()
    {
        list($model) = $this->prepareConfigModelForAttributes();
        $this->assertEquals(['position' => 'Position', 'code' => 'label'], $model->getAttributeUsedForSortByArray());
    }

    public function testGetProductListDefaultSortBy()
    {
        $scopeConfig = $this->createPartialMock(
            ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );
        $scopeConfig->expects($this->once())->method('getValue')
            ->with('catalog/frontend/default_sort_by', 'store', null)->willReturn(1);
        $objectManager = new ObjectManager($this);
        $model = $objectManager->getObject(Config::class, ['scopeConfig' => $scopeConfig]);
        $this->assertEquals(1, $model->getProductListDefaultSortBy());
    }
}

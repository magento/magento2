<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Catalog\Model\Entity\Attribute\Set
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\AbstractResource;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractTest extends TestCase
{
    use MockCreationTrait;

    /**
     * Get attribute list
     *
     * @return array
     */
    protected function _getAttributes()
    {
        $attributes = [];
        $codes = ['entity_type_id', 'attribute_set_id', 'created_at', 'updated_at', 'parent_id', 'increment_id'];
        foreach ($codes as $code) {
            $mock = $this->createPartialMock(AbstractAttribute::class, ['isInSet', 'getBackend']);

            $mock->setAttributeId($code);
            $mock->setAttributeCode($code);

            $mock->expects($this->once())->method('isInSet')->willReturn(false);

            $attributes[$code] = $mock;
        }
        return $attributes;
    }

    public function testWalkAttributes()
    {
        $code = 'test_attr';
        $set = 10;
        $storeId = 100;

        $object = $this->createPartialMock(Product::class, ['__wakeup']);

        $object->setData('test_attr', 'test_attr');
        $object->setData('attribute_set_id', $set);
        $object->setData('store_id', $storeId);

        $entityType = new DataObject();
        $entityType->setEntityTypeCode('test');
        $entityType->setEntityTypeId(0);
        $entityType->setEntityTable('table');

        $attributes = $this->_getAttributes();

        $attribute = $this->createPartialMock(
            AbstractAttribute::class,
            ['isInSet', 'getBackend']
        );
        $attribute->setAttributeId($code);
        $attribute->setAttributeCode($code);

        $attribute->expects(
            $this->once()
        )->method(
            'isInSet'
        )->with(
            $set
        )->willReturn(
            false
        );

        $attributes[$code] = $attribute;

        /** @var AbstractResource $model */
        $model = $this->createPartialMockWithReflection(
            AbstractResource::class,
            ['getAttributesByCode']
        );

        $model->expects($this->once())->method('getAttributesByCode')->willReturn($attributes);

        $model->walkAttributes('backend/afterSave', [$object]);
    }

    /**
     * Verify which updates can use the direct update optimization or the fallback.
     *
     * @param bool $singleStore
     * @param int $scope
     * @param int $storeId
     * @param array $row
     * @param int $valueId
     * @param bool $directUpdate
     * @return void
     */
    #[DataProvider('updateProvider')]
    public function testUpdateAttribute(
        bool $singleStore,
        int $scope,
        int $storeId,
        array $row,
        int $valueId,
        bool $directUpdate
    ): void {
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('hasSingleStore')->willReturn($singleStore);
        $store = $this->createStub(Store::class);
        $store->method('getId')->willReturn($storeId);
        $storeManager->method('getStore')->willReturn($store);
        $connection = $this->createMock(AdapterInterface::class);
        $backend = $this->createStub(AbstractBackend::class);
        $backend->method('getTable')->willReturn('catalog_product_entity_int');
        $attribute = $this->createStub(Attribute::class);
        $attribute->method('isScopeWebsite')->willReturn($scope === ScopedAttributeInterface::SCOPE_WEBSITE);
        $attribute->method('getBackend')->willReturn($backend);
        $object = new DataObject(['store_id' => $storeId]);
        $resource = $this->createUpdateResource($storeManager, $connection, $row);

        if ($directUpdate) {
            $resource->expects($this->never())->method('_saveAttributeValue');
            $resource->expects($this->once())->method('_prepareValueForSave')->with(1, $attribute)->willReturn(1);
            $connection->method('quoteIdentifier')->with('value_id')->willReturn('`value_id`');
            $connection->expects($this->once())->method('update')->with(
                'catalog_product_entity_int',
                ['value' => 1],
                '`value_id`=' . $valueId
            );
        } else {
            $connection->expects($this->never())->method('update');
            $resource->expects($this->once())->method('_saveAttributeValue')
                ->with($object, $attribute, 1)->willReturnSelf();
        }

        $method = new \ReflectionMethod(AbstractResource::class, '_updateAttribute');
        self::assertSame($resource, $method->invoke($resource, $object, $attribute, $valueId, 1));
    }

    /**
     * Verify website updates use one UPDATE and insert only missing rows.
     *
     * @param array $existingStoreIds
     * @param array $insertRows
     * @return void
     */
    #[DataProvider('websiteUpdateProvider')]
    public function testUpdateWebsiteAttribute(array $existingStoreIds, array $insertRows): void
    {
        $website = $this->createStub(Website::class);
        $website->method('getStoreIds')->willReturn([2, 3]);
        $store = $this->createStub(Store::class);
        $store->method('getId')->willReturn(2);
        $store->method('getWebsite')->willReturn($website);
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('hasSingleStore')->willReturn(false);
        $storeManager->method('getStore')->willReturn($store);
        $connection = $this->createMock(AdapterInterface::class);
        $select = $this->createStub(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $connection->method('select')->willReturn($select);
        $connection->expects($this->once())->method('fetchCol')->with($select)->willReturn($existingStoreIds);
        $connection->expects($this->once())->method('update')->with(
            'catalog_product_entity_int',
            ['value' => 1],
            ['entity_id = ?' => 42, 'attribute_id = ?' => 13, 'store_id IN (?)' => [2, 3]]
        );
        if ($insertRows) {
            $connection->expects($this->once())->method('insertOnDuplicate')
                ->with('catalog_product_entity_int', $insertRows, ['value']);
        } else {
            $connection->expects($this->never())->method('insertOnDuplicate');
        }
        $backend = $this->createStub(AbstractBackend::class);
        $backend->method('getTable')->willReturn('catalog_product_entity_int');
        $attribute = $this->createStub(Attribute::class);
        $attribute->method('isScopeWebsite')->willReturn(true);
        $attribute->method('getBackend')->willReturn($backend);
        $attribute->method('getId')->willReturn(13);
        $resource = $this->createUpdateResource($storeManager, $connection, ['store_id' => 2]);
        $resource->method('getLinkField')->willReturn('entity_id');
        $resource->expects($this->once())->method('_prepareValueForSave')->with(1, $attribute)->willReturn(1);
        $resource->expects($this->never())->method('_saveAttributeValue');
        $object = new DataObject(['store_id' => 2, 'entity_id' => 42]);

        $method = new \ReflectionMethod(AbstractResource::class, '_updateAttribute');
        self::assertSame($resource, $method->invoke($resource, $object, $attribute, 7, 1));
    }

    /**
     * Build a concrete resource with controlled attribute storage dependencies.
     *
     * @param StoreManagerInterface $storeManager
     * @param AdapterInterface $connection
     * @param array $row
     * @return ProductResource&MockObject
     */
    private function createUpdateResource(
        StoreManagerInterface $storeManager,
        AdapterInterface $connection,
        array $row
    ): ProductResource {
        $resource = $this->getMockBuilder(ProductResource::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getAttributeRow',
                'getConnection',
                'getLinkField',
                '_prepareValueForSave',
                '_saveAttributeValue',
            ])
            ->getMock();
        $property = new \ReflectionProperty(AbstractResource::class, '_storeManager');
        $property->setValue($resource, $storeManager);
        $resource->method('getAttributeRow')->willReturn($row);
        $resource->method('getConnection')->willReturn($connection);
        return $resource;
    }

    /**
     * Cases protecting the existing update optimization and fallback.
     *
     * @return array
     */
    public static function updateProvider(): array
    {
        $website = ScopedAttributeInterface::SCOPE_WEBSITE;
        $store = ScopedAttributeInterface::SCOPE_STORE;
        $global = ScopedAttributeInterface::SCOPE_GLOBAL;
        return [
            'store scope' => [false, $store, 2, ['store_id' => 2], 7, true],
            'global scope' => [false, $global, 0, ['store_id' => 0], 7, true],
            'website admin' => [false, $website, 0, ['store_id' => 0], 7, true],
            'single store admin row' => [true, $website, 0, ['store_id' => 0], 7, true],
            'single store nondefault row' => [true, $website, 2, ['store_id' => 2], 7, false],
            'missing value' => [false, $store, 2, ['store_id' => 2], 0, false],
            'missing store key' => [false, $store, 2, [], 7, false],
            'different store' => [false, $store, 2, ['store_id' => 3], 7, false],
        ];
    }

    /**
     * Cover complete and incomplete sets of website rows.
     *
     * @return array
     */
    public static function websiteUpdateProvider(): array
    {
        $secondRow = ['entity_id' => 42, 'attribute_id' => 13, 'store_id' => 3, 'value' => 1];
        return [
            'all rows exist' => [[2, 3], []],
            'sibling missing' => [[2], [$secondRow]],
            'all rows missing' => [
                [],
                [
                    ['entity_id' => 42, 'attribute_id' => 13, 'store_id' => 2, 'value' => 1],
                    $secondRow,
                ],
            ],
        ];
    }
}

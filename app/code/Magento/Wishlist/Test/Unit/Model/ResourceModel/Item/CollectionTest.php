<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Model\ResourceModel\Item;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    use \Magento\Framework\TestFramework\Unit\Helper\SelectRendererTrait;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var string */
    protected $attrTableName = 'testBackendTableName';

    /** @var int */
    protected $attrId = 12;

    /** @var int */
    protected $storeId = 1;

    /** @var  string */
    protected $sql = "SELECT `main_table`.* FROM `testMainTableName` AS `main_table`
 INNER JOIN `testBackendTableName` AS `product_name_table` ON product_name_table.entity_id = main_table.product_id
 AND product_name_table.store_id = 1
 AND product_name_table.attribute_id = 12
 WHERE (INSTR(product_name_table.value, 'TestProductName'))";

    /**
     * @var MetadataPool|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $metadataPool;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $connection = $this->createPartialMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, ['quote', 'select']);
        $select = new \Magento\Framework\DB\Select($connection, $this->getSelectRenderer($this->objectManager));
        $connection
            ->expects($this->any())
            ->method('quote')
            ->willReturn('\'TestProductName\'');
        $connection
            ->expects($this->any())
            ->method('select')
            ->willReturn($select);
        $resource = $this->createPartialMock(
            \Magento\Wishlist\Model\ResourceModel\Item::class,
            ['getConnection', 'getMainTable', 'getTableName', 'getTable']
        );

        $resource
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);
        $resource
            ->expects($this->any())
            ->method('getMainTable')
            ->willReturn('testMainTableName');
        $resource
            ->expects($this->any())
            ->method('getTableName')
            ->willReturn('testMainTableName');
        $resource
            ->expects($this->any())
            ->method('getTable')
            ->willReturn('testMainTableName');

        $catalogConfFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\ConfigFactory::class,
            ['create']
        );

        $catalogConf = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Config::class,
            ['getEntityTypeId']
        );
        $catalogConf
            ->expects($this->once())
            ->method('getEntityTypeId')
            ->willReturn(4);

        $catalogConfFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($catalogConf);

        $attribute = $this->createPartialMock(
            \Magento\Catalog\Model\Entity\Attribute::class,
            ['loadByCode', 'getBackendTable', 'getId']
        );
        $attribute
            ->expects($this->once())
            ->method('loadByCode')
            ->with(4, 'name')
            ->willReturnSelf();
        $attribute
            ->expects($this->once())
            ->method('getBackendTable')
            ->willReturn($this->attrTableName);
        $attribute
            ->expects($this->once())
            ->method('getId')
            ->willReturn($this->attrId);

        $catalogAttrFactory = $this->createPartialMock(
            \Magento\Catalog\Model\Entity\AttributeFactory::class,
            ['create']
        );

        $catalogAttrFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($attribute);

        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId']);
        $store
            ->expects($this->once())
            ->method('getId')
            ->willReturn($this->storeId);

        $storeManager = $this->createPartialMock(\Magento\Store\Model\StoreManager::class, ['getStore']);
        $storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $this->collection = $this->objectManager->getObject(
            \Magento\Wishlist\Model\ResourceModel\Item\Collection::class,
            [
                'resource' => $resource,
                'catalogConfFactory' => $catalogConfFactory,
                'catalogAttrFactory' => $catalogAttrFactory,
                'storeManager' => $storeManager
            ]
        );

        $this->metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $reflection = new \ReflectionClass(get_class($this->collection));
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->collection, $this->metadataPool);
    }

    public function testAddProductNameFilter()
    {
        $entityMetadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityMetadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn('entity_id');

        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($entityMetadata);

        $collection = $this->collection->addProductNameFilter('TestProductName');
        $sql = $collection->getSelect()->__toString();
        $sql = trim(preg_replace('/\s+/', ' ', $sql));
        $this->assertEquals(trim(preg_replace('/\s+/', ' ', $this->sql)), $sql);
    }
}

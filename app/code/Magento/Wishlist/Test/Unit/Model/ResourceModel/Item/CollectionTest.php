<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Wishlist\Test\Unit\Model\ResourceModel\Item;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\Entity\AttributeFactory;
use Magento\Catalog\Model\ResourceModel\Config;
use Magento\Catalog\Model\ResourceModel\ConfigFactory;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\SelectRendererTrait;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Wishlist\Model\ResourceModel\Item;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    use SelectRendererTrait;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var ObjectManager
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
     * @var MetadataPool|MockObject
     */
    protected $metadataPool;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $connection = $this->createPartialMock(Mysql::class, ['quote', 'select']);
        $select = new Select($connection, $this->getSelectRenderer($this->objectManager));
        $connection
            ->expects($this->any())
            ->method('quote')
            ->willReturn('\'TestProductName\'');
        $connection
            ->expects($this->any())
            ->method('select')
            ->willReturn($select);
        $resource = $this->getMockBuilder(Item::class)
            ->addMethods(['getTableName'])
            ->onlyMethods(['getConnection', 'getMainTable', 'getTable'])
            ->disableOriginalConstructor()
            ->getMock();

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
            ConfigFactory::class,
            ['create']
        );

        $catalogConf = $this->createPartialMock(
            Config::class,
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
            Attribute::class,
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
            AttributeFactory::class,
            ['create']
        );

        $catalogAttrFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($attribute);

        $store = $this->createPartialMock(Store::class, ['getId']);
        $store
            ->expects($this->once())
            ->method('getId')
            ->willReturn($this->storeId);

        $storeManager = $this->createPartialMock(StoreManager::class, ['getStore']);
        $storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $this->collection = $this->objectManager->getObject(
            Collection::class,
            [
                'resource' => $resource,
                'catalogConfFactory' => $catalogConfFactory,
                'catalogAttrFactory' => $catalogAttrFactory,
                'storeManager' => $storeManager
            ]
        );

        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $reflection = new \ReflectionClass(get_class($this->collection));
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->collection, $this->metadataPool);
    }

    public function testAddProductNameFilter()
    {
        $entityMetadata = $this->getMockBuilder(EntityMetadata::class)
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

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Model\ResourceModel\Item;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\Collection
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
 INNER JOIN `testBackendTableName` AS `product_name_table` ON product_name_table.entity_id=main_table.product_id
 AND product_name_table.store_id=1
 AND product_name_table.attribute_id=12
 WHERE (INSTR(product_name_table.value, 'TestProductName'))";

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $connection = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['quote'],
            [],
            '',
            false
        );
        $connection
            ->expects($this->any())
            ->method('quote')
            ->will($this->returnValue('\'TestProductName\''));

        $resource = $this->getMock(
            'Magento\Wishlist\Model\ResourceModel\Item',
            ['getConnection', 'getMainTable', 'getTableName', 'getTable'],
            [],
            '',
            false
        );

        $resource
            ->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connection));
        $resource
            ->expects($this->any())
            ->method('getMainTable')
            ->will($this->returnValue('testMainTableName'));
        $resource
            ->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue('testMainTableName'));
        $resource
            ->expects($this->any())
            ->method('getTable')
            ->will($this->returnValue('testMainTableName'));

        $catalogConfFactory = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\ConfigFactory',
            ['create'],
            [],
            '',
            false
        );

        $catalogConf = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Config',
            ['getEntityTypeId'],
            [],
            '',
            false
        );
        $catalogConf
            ->expects($this->once())
            ->method('getEntityTypeId')
            ->will($this->returnValue(4));


        $catalogConfFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($catalogConf));

        $attribute = $this->getMock(
            'Magento\Catalog\Model\Entity\Attribute',
            ['loadByCode', 'getBackendTable', 'getId'],
            [],
            '',
            false
        );
        $attribute
            ->expects($this->once())
            ->method('loadByCode')
            ->with(4, 'name')
            ->will($this->returnSelf());
        $attribute
            ->expects($this->once())
            ->method('getBackendTable')
            ->will($this->returnValue($this->attrTableName));
        $attribute
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($this->attrId));


        $catalogAttrFactory = $this->getMock(
            'Magento\Catalog\Model\Entity\AttributeFactory',
            ['create'],
            [],
            '',
            false
        );
        $catalogAttrFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($attribute));

        $store = $this->getMock(
            'Magento\Store\Model\Store',
            ['getId'],
            [],
            '',
            false
        );
        $store
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($this->storeId));

        $storeManager = $this->getMock(
            'Magento\Store\Model\StoreManager',
            ['getStore'],
            [],
            '',
            false
        );
        $storeManager
            ->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($store));

        $this->collection = $this->objectManager->getObject(
            'Magento\Wishlist\Model\ResourceModel\Item\Collection',
            [
                'resource' => $resource,
                'catalogConfFactory' => $catalogConfFactory,
                'catalogAttrFactory' => $catalogAttrFactory,
                'storeManager' => $storeManager
            ]
        );
    }

    public function testAddProductNameFilter()
    {
        $collection = $this->collection->addProductNameFilter('TestProductName');
        $sql = $collection->getSelect()->__toString();
        $sql = trim(preg_replace('/\s+/', ' ', $sql));
        $this->assertEquals(trim(preg_replace('/\s+/', ' ', $this->sql)), $sql);
    }
}

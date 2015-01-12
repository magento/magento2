<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

class StockTest extends \PHPUnit_Framework_TestCase
{
    const ATTRIBUTE_NAME = 'quantity_and_stock_status';

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Stock
     */
    protected $model;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    protected function setUp()
    {
        $this->objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->stockRegistry = $this->getMockBuilder('Magento\CatalogInventory\Model\StockRegistry')
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->getMock(
            'Magento\CatalogInventory\Model\Stock\Item',
            ['getIsInStock', 'getQty', '__wakeup'],
            [],
            '',
            false
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));
        $this->model = $this->objectHelper->getObject(
            'Magento\Catalog\Model\Product\Attribute\Backend\Stock',
            ['stockRegistry' => $this->stockRegistry]
        );
        $attribute = $this->getMock('Magento\Framework\Object', ['getAttributeCode']);
        $attribute->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->will($this->returnValue(self::ATTRIBUTE_NAME));
        $this->model->setAttribute($attribute);
    }

    public function testAfterLoad()
    {
        $productId = 2;

        $this->stockItemMock->expects($this->once())->method('getIsInStock')->will($this->returnValue(1));
        $this->stockItemMock->expects($this->once())->method('getQty')->will($this->returnValue(5));

        $store = $this->getMock('Magento\Store\Model\Store', ['getWebsiteId', '__wakeup'], [], '', false);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue(10));
        $object = new \Magento\Framework\Object(['id' => $productId, 'store' => $store]);
        $this->model->afterLoad($object);
        $data = $object->getData();
        $this->assertEquals(1, $data[self::ATTRIBUTE_NAME]['is_in_stock']);
        $this->assertEquals(5, $data[self::ATTRIBUTE_NAME]['qty']);
    }

    public function testBeforeSave()
    {
        $object = new \Magento\Framework\Object(
            [
                self::ATTRIBUTE_NAME => ['is_in_stock' => 1, 'qty' => 5],
                'stock_data' => ['is_in_stock' => 2, 'qty' => 2],
            ]
        );
        $stockData = $object->getStockData();
        $this->assertEquals(2, $stockData['is_in_stock']);
        $this->assertEquals(2, $stockData['qty']);
        $this->assertNotEmpty($object->getData(self::ATTRIBUTE_NAME));

        $this->model->beforeSave($object);

        $stockData = $object->getStockData();
        $this->assertEquals(1, $stockData['is_in_stock']);
        $this->assertEquals(5, $stockData['qty']);
        $this->assertNull($object->getData(self::ATTRIBUTE_NAME));
    }

    public function testBeforeSaveQtyIsEmpty()
    {
        $object = new \Magento\Framework\Object(
            [
                self::ATTRIBUTE_NAME => ['is_in_stock' => 1, 'qty' => ''],
                'stock_data' => ['is_in_stock' => 2, 'qty' => ''],
            ]
        );

        $this->model->beforeSave($object);

        $stockData = $object->getStockData();
        $this->assertNull($stockData['qty']);
    }

    public function testBeforeSaveQtyIsZero()
    {
        $object = new \Magento\Framework\Object(
            [
                self::ATTRIBUTE_NAME => ['is_in_stock' => 1, 'qty' => 0],
                'stock_data' => ['is_in_stock' => 2, 'qty' => 0],
            ]
        );

        $this->model->beforeSave($object);

        $stockData = $object->getStockData();
        $this->assertEquals(0, $stockData['qty']);
    }
}

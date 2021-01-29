<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

class StockTest extends \PHPUnit\Framework\TestCase
{
    const ATTRIBUTE_NAME = 'quantity_and_stock_status';

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Stock
     */
    protected $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockRegistry;

    protected function setUp(): void
    {
        $this->objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->stockRegistry = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getIsInStock', 'getQty', '__wakeup']
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->model = $this->objectHelper->getObject(
            \Magento\Catalog\Model\Product\Attribute\Backend\Stock::class,
            ['stockRegistry' => $this->stockRegistry]
        );
        $attribute = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getAttributeCode']);
        $attribute->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn(self::ATTRIBUTE_NAME);
        $this->model->setAttribute($attribute);
    }

    public function testAfterLoad()
    {
        $productId = 2;

        $this->stockItemMock->expects($this->once())->method('getIsInStock')->willReturn(1);
        $this->stockItemMock->expects($this->once())->method('getQty')->willReturn(5);

        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId', '__wakeup']);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(10);
        $object = new \Magento\Framework\DataObject(['id' => $productId, 'store' => $store]);
        $this->model->afterLoad($object);
        $data = $object->getData();
        $this->assertEquals(1, $data[self::ATTRIBUTE_NAME]['is_in_stock']);
        $this->assertEquals(5, $data[self::ATTRIBUTE_NAME]['qty']);
    }
}

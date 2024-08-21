<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product\Attribute\Backend\Stock;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StockTest extends TestCase
{
    private const ATTRIBUTE_NAME = 'quantity_and_stock_status';

    /**
     * @var Stock
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectHelper;

    /** @var MockObject */
    protected $stockItemMock;

    /**
     * @var MockObject
     */
    protected $stockRegistry;

    protected function setUp(): void
    {
        $this->objectHelper = new ObjectManager($this);
        $this->stockRegistry = $this->getMockBuilder(StockRegistry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStockItem'])
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            Item::class,
            ['getIsInStock', 'getQty']
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->model = $this->objectHelper->getObject(
            Stock::class,
            ['stockRegistry' => $this->stockRegistry]
        );
        $attribute = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(10);
        $object = new DataObject(['id' => $productId, 'store' => $store]);
        $this->model->afterLoad($object);
        $data = $object->getData();
        $this->assertEquals(1, $data[self::ATTRIBUTE_NAME]['is_in_stock']);
        $this->assertEquals(5, $data[self::ATTRIBUTE_NAME]['qty']);
    }
}

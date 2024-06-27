<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\AdminOrder\Product\Quote;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\AdminOrder\Product\Quote\Initializer;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InitializerTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Quote|MockObject
     */
    protected $quoteMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var DataObject|MockObject
     */
    protected $configMock;

    /**
     * @var Initializer
     */
    protected $model;

    /** @var MockObject */
    protected $stockItemMock;

    /**
     * @var MockObject
     */
    protected $stockRegistry;

    protected function setUp(): void
    {
        $this->quoteMock = $this->createPartialMock(
            Quote::class,
            ['addProduct', 'getStore']
        );

        $this->productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['setIsQtyDecimal', 'setCartQty'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getQty', 'setQty'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockRegistry = $this->getMockBuilder(StockRegistry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStockItem'])
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            Item::class,
            ['getIsQtyDecimal']
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(10);
        $this->quoteMock->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager
            ->getObject(
                Initializer::class,
                ['stockRegistry' => $this->stockRegistry]
            );
    }

    public function testInitWithDecimalQty()
    {
        $quoteItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)->addMethods(
            ['getStockId', 'getIsQtyDecimal']
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockItemMock->expects($this->once())
            ->method('getIsQtyDecimal')
            ->willReturn(10);

        $this->productMock->expects($this->once())
            ->method('getId')->willReturnSelf();

        $this->productMock->expects($this->once())
            ->method('setIsQtyDecimal')->willReturnSelf();

        $this->productMock->expects($this->once())
            ->method('setCartQty')->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getQty')
            ->willReturn(20);

        $this->configMock->expects($this->never())
            ->method('setQty');

        $this->quoteMock->expects($this->once())
            ->method('addProduct')
            ->willReturn($quoteItemMock);

        $this->assertInstanceOf(
            \Magento\Quote\Model\Quote\Item::class,
            $this->model->init(
                $this->quoteMock,
                $this->productMock,
                $this->configMock
            )
        );
    }

    public function testInitWithNonDecimalQty()
    {
        $quoteItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)->addMethods(
            ['getStockId', 'getIsQtyDecimal']
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->productMock->expects($this->once())
            ->method('getId')->willReturnSelf();

        $this->productMock->expects($this->never())
            ->method('setIsQtyDecimal');

        $this->productMock->expects($this->once())
            ->method('setCartQty')->willReturnSelf();

        $this->configMock->expects($this->exactly(2))
            ->method('getQty')
            ->willReturn(10);

        $this->configMock->expects($this->once())
            ->method('setQty')->willReturnSelf();

        $this->quoteMock->expects($this->once())
            ->method('addProduct')
            ->willReturn($quoteItemMock);

        $this->assertInstanceOf(
            \Magento\Quote\Model\Quote\Item::class,
            $this->model->init(
                $this->quoteMock,
                $this->productMock,
                $this->configMock
            )
        );
    }
}

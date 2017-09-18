<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\AdminOrder\Product\Quote;

/**
 * Initializer test
 */
class InitializerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Sales\Model\AdminOrder\Product\Quote\Initializer
     */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    protected function setUp()
    {
        $this->quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['addProduct', '__wakeup', 'getStore']
        );

        $this->productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getId', 'setIsQtyDecimal', 'setCartQty', '__wakeup']
        );

        $this->configMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getQty', 'setQty']);

        $this->stockRegistry = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getIsQtyDecimal', '__wakeup']
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId']);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue(10));
        $this->quoteMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $this->objectManager
            ->getObject(
                \Magento\Sales\Model\AdminOrder\Product\Quote\Initializer::class,
                ['stockRegistry' => $this->stockRegistry]
            );
    }

    public function testInitWithDecimalQty()
    {
        $quoteItemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getStockId', 'getIsQtyDecimal', '__wakeup']
        );

        $this->stockItemMock->expects($this->once())
            ->method('getIsQtyDecimal')
            ->will($this->returnValue(10));

        $this->productMock->expects($this->once())
            ->method('getId')
            ->will($this->returnSelf());

        $this->productMock->expects($this->once())
            ->method('setIsQtyDecimal')
            ->will($this->returnSelf());

        $this->productMock->expects($this->once())
            ->method('setCartQty')
            ->will($this->returnSelf());

        $this->configMock->expects($this->once())
            ->method('getQty')
            ->will($this->returnValue(20));

        $this->configMock->expects($this->never())
            ->method('setQty');

        $this->quoteMock->expects($this->once())
            ->method('addProduct')
            ->will($this->returnValue($quoteItemMock));

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
        $quoteItemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getStockId', 'getIsQtyDecimal', '__wakeup']
        );

        $this->productMock->expects($this->once())
            ->method('getId')
            ->will($this->returnSelf());

        $this->productMock->expects($this->never())
            ->method('setIsQtyDecimal');

        $this->productMock->expects($this->once())
            ->method('setCartQty')
            ->will($this->returnSelf());

        $this->configMock->expects($this->exactly(2))
            ->method('getQty')
            ->will($this->returnValue(10));

        $this->configMock->expects($this->once())
            ->method('setQty')
            ->will($this->returnSelf());

        $this->quoteMock->expects($this->once())
            ->method('addProduct')
            ->will($this->returnValue($quoteItemMock));

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

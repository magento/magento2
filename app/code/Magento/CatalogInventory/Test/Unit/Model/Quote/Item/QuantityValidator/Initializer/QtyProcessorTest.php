<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\QtyProcessor;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QtyProcessorTest extends TestCase
{
    /**
     * @var QtyProcessor
     */
    protected $qtyProcessor;

    /**
     * @var MockObject
     */
    protected $quoteItemQtyList;

    /**
     * @var MockObject
     */
    protected $itemMock;

    protected function setUp(): void
    {
        $this->quoteItemQtyList = $this->getMockBuilder(
            QuoteItemQtyList::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->qtyProcessor = new QtyProcessor($this->quoteItemQtyList);
        $this->itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentItem', 'getProduct', '__wakeup'])
            ->getMock();
    }

    public function testSetItem()
    {
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(
            QtyProcessor::class,
            $this->qtyProcessor->setItem($itemMock)
        );
    }

    public function testGetRowQty()
    {
        $qty = 1;

        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn($parentItemMock);
        $parentItemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($qty);

        $this->qtyProcessor->setItem($itemMock);
        $this->assertEquals($qty, $this->qtyProcessor->getRowQty($qty));
    }

    public function testGetQtyForCheckNoParentItem()
    {
        $qty = 1;
        $productId = 1;

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productMock);
        $this->quoteItemQtyList->expects($this->once())
            ->method('getQty')
            ->withAnyParameters()
            ->willReturn($qty);

        $this->qtyProcessor->setItem($itemMock);
        $this->assertEquals($qty, $this->qtyProcessor->getQtyForCheck($qty));
    }

    public function testGetQtyForCheck()
    {
        $qty = 1;
        $productId = 1;

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $parentItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn($parentItemMock);
        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productMock);
        $this->quoteItemQtyList->expects($this->once())
            ->method('getQty')
            ->withAnyParameters()
            ->willReturn($qty);

        $this->qtyProcessor->setItem($this->itemMock);
        $this->assertEquals($qty, $this->qtyProcessor->getQtyForCheck($qty));
    }
}

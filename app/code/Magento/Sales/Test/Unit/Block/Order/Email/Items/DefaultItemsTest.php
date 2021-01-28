<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Block\Order\Email\Items;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Quote\Model\Quote\Item  as QuoteItem;
use Magento\Sales\Block\Order\Email\Items\DefaultItems;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\TestCase;

class DefaultItemsTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Sales\Block\Order\Email\Items\DefaultItem
     */
    protected $block;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Template
     */
    protected $priceRenderBlock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Layout
     */
    protected $layoutMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|Item */
    protected $itemMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|QuoteItem */
    protected $quoteItemMock;

    /**
     * Initialize required data
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBlock'])
            ->getMock();

        $this->priceRenderBlock = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->setMethods(['setItem','toHtml'])
            ->getMock();

        $this->itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup','setRowTotal', 'setBaseRowTotal', 'getPrice', 'getBasePrice'])
            ->getMock();

        $this->quoteItemMock = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQty'])
            ->getMock();

        $this->block = $this->objectManager->getObject(
            DefaultItems::class,
            [
                'context' => $this->objectManager->getObject(
                    Context::class,
                    ['layout' => $this->layoutMock]
                ),
                'data' => [
                    'item' => $this->quoteItemMock
                ]
            ]
        );
    }

    /**
     * @param float $price
     * @param string $html
     * @param float $quantity
     * @dataProvider getItemPriceDataProvider
     * */
    public function testGetItemPrice($price, $html, $quantity)
    {
        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_price')
            ->willReturn($this->priceRenderBlock);
        $this->quoteItemMock->expects($this->any())
            ->method('getQty')
            ->willReturn($quantity);
        $this->itemMock->expects($this->any())
            ->method('setRowTotal')
            ->willReturn($price * $quantity);
        $this->itemMock->expects($this->any())
            ->method('setBaseRowTotal')
            ->willReturn($price * $quantity);

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->willReturn($html);

        $this->assertEquals($html, $this->block->getItemPrice($this->itemMock));
    }

    /**
     * @return array
     */
    public function getItemPriceDataProvider()
    {
        return [
            'get default item price' => [34.28,'$34.28',1.0],
            'get item price with quantity 2.0' => [12.00,'$24.00',2.0]
        ];
    }
}

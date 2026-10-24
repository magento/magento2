<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Order\Email\Items;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Block\Order\Email\Items\DefaultItems;
use Magento\Sales\Model\Order\Item as OrderItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class DefaultItemsTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var MockObject|DefaultItems
     */
    protected $block;

    /**
     * @var MockObject|Template
     */
    protected $priceRenderBlock;

    /**
     * @var MockObject|Layout
     */
    protected $layoutMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject|OrderItem
     */
    protected $itemMock;

    /**
     * @var MockObject|QuoteItem
     */
    protected $quoteItemMock;

    /**
     * Initialize required data
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBlock'])
            ->getMock();

        $this->priceRenderBlock = $this->createPartialMockWithReflection(
            Template::class,
            ['setItem', 'toHtml']
        );

        $this->itemMock = $this->createMock(OrderItem::class);

        $this->quoteItemMock = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQty'])
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
     */
    #[DataProvider('getItemPriceDataProvider')]
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
    public static function getItemPriceDataProvider()
    {
        return [
            'get default item price' => [34.28,'$34.28',1.0],
            'get item price with quantity 2.0' => [12.00,'$24.00',2.0]
        ];
    }
}

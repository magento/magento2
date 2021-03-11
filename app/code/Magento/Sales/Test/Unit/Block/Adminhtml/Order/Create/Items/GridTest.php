<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create\Items;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid
     */
    protected $block;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Backend\Block\Template
     */
    protected $priceRenderBlock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\Layout
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Quote\Model\Quote\Item  */
    protected $itemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockRegistry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockState;

    /**
     * Initialize required data
     */
    protected function setUp(): void
    {
        $orderCreateMock = $this->createPartialMock(\Magento\Sales\Model\AdminOrder\Create::class, ['__wakeup']);
        $taxData = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)->disableOriginalConstructor()->getMock();
        $this->priceCurrency = $this->getMockBuilder(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        )->getMock();
        $sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', '__wakeup'])
            ->getMock();

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup'])
            ->getMock();

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
        $this->priceCurrency->expects($this->any())
            ->method('convertAndFormat')
            ->willReturnArgument(0);
        $quoteMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $sessionMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $wishlistFactoryMock = $this->getMockBuilder(\Magento\Wishlist\Model\WishlistFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['methods', '__wakeup'])
            ->getMock();

        $giftMessageSave = $this->getMockBuilder(\Magento\Giftmessage\Model\Save::class)
            ->setMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $taxConfig = $this->getMockBuilder(\Magento\Tax\Model\Config::class)->disableOriginalConstructor()->getMock();

        $this->stockRegistry = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getIsInStock', '__wakeup']
        );

        $this->stockState = $this->createPartialMock(
            \Magento\CatalogInventory\Model\StockState::class,
            ['checkQuoteItemQty', '__wakeup']
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $this->objectManager->getObject(
            \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid::class,
            [
                'wishlistFactory' => $wishlistFactoryMock,
                'giftMessageSave' => $giftMessageSave,
                'taxConfig' => $taxConfig,
                'taxData' => $taxData,
                'sessionQuote' => $sessionMock,
                'orderCreate' => $orderCreateMock,
                'priceCurrency' => $this->priceCurrency,
                'stockRegistry' => $this->stockRegistry,
                'stockState' => $this->stockState
            ]
        );

        $this->priceRenderBlock = $this->getMockBuilder(\Magento\Backend\Block\Template::class)
            ->disableOriginalConstructor()
            ->setMethods(['setItem', 'toHtml'])
            ->getMock();

        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBlock'])
            ->getMock();

        $this->itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
    }

    /**
     * @param array $itemData
     * @param string $expectedMessage
     * @param string $productType
     * @dataProvider tierPriceDataProvider
     */
    public function testTierPriceInfo($itemData, $expectedMessage, $productType)
    {
        $itemMock = $this->prepareItem($itemData, $productType);
        $result = $this->block->getTierHtml($itemMock);
        $this->assertEquals($expectedMessage, $result);
    }

    /**
     * Provider for test
     *
     * @return array
     */
    public function tierPriceDataProvider()
    {
        return [
            [
                [['price' => 100, 'price_qty' => 1]],
                '1 with 100% discount each',
                \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
            ],
            [
                [['price' => 100, 'price_qty' => 1], ['price' => 200, 'price_qty' => 2]],
                '1 with 100% discount each<br />2 with 200% discount each',
                \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            ],
            [
                [['price' => 50, 'price_qty' => 2]],
                '2 for 50',
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            ],
            [
                [['price' => 50, 'price_qty' => 2], ['price' => 150, 'price_qty' => 3]],
                '2 for 50<br />3 for 150',
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            ],
            [0, '', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE]
        ];
    }

    /**
     * @param array|int $tierPrices
     * @param string $productType
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Quote\Model\Quote\Item
     */
    protected function prepareItem($tierPrices, $productType)
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTierPrice', '__wakeup', 'getStatus'])
            ->getMock();
        $product->expects($this->once())->method('getTierPrice')->willReturn($tierPrices);
        $item = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setConstructorArgs(['getProduct', 'getProductType'])
            ->disableOriginalConstructor()
            ->getMock();
        $item->expects($this->once())->method('getProduct')->willReturn($product);

        $calledTimes = $tierPrices ? 'once' : 'never';
        $item->expects($this->{$calledTimes}())->method('getProductType')->willReturn($productType);
        return $item;
    }

    /**
     * @covers \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid::getItems
     */
    public function testGetItems()
    {
        $productId = 8;
        $itemQty = 23;
        $layoutMock = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $blockMock = $this->createPartialMock(\Magento\Framework\View\Element\AbstractBlock::class, ['getItems']);

        $itemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProduct', 'setHasError', 'setQty', 'getQty', '__sleep', '__wakeup', 'getChildren']
        );
        $productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getStockItem', 'getID', '__sleep', '__wakeup', 'getStatus']
        );

        $checkMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getMessage', 'getHasError']);

        $layoutMock->expects($this->once())->method('getParentName')->willReturn('parentBlock');
        $layoutMock->expects($this->once())->method('getBlock')->with('parentBlock')
            ->willReturn($blockMock);

        $blockMock->expects($this->once())->method('getItems')->willReturn([$itemMock]);

        $itemMock->expects($this->any())->method('getChildren')->willReturn([$itemMock]);
        $itemMock->expects($this->any())->method('getProduct')->willReturn($productMock);
        $itemMock->expects($this->any())->method('getQty')->willReturn($itemQty);

        $productMock->expects($this->any())->method('getId')->willReturn($productId);
        $productMock->expects($this->any())->method('getStatus')
            ->willReturn(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

        $checkMock->expects($this->any())->method('getMessage')->willReturn('Message');
        $checkMock->expects($this->any())->method('getHasError')->willReturn(false);

        $this->stockState->expects($this->once())
            ->method('checkQuoteItemQty')
            ->with(
                $productId,
                $itemQty,
                $itemQty,
                $itemQty,
                null
            )
            ->willReturn($checkMock);

        $this->block->getQuote()->setIsSuperMode(true);
        $items = $this->block->setLayout($layoutMock)->getItems();

        $this->assertEquals('Message', $items[0]->getMessage());
        $this->assertTrue($this->block->getQuote()->getIsSuperMode());
    }

    /**
     * @return \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid
     */
    protected function getGrid()
    {
        /** @var \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid  $grid */
        $grid = $this->objectManager->getObject(
            \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid::class,
            [
                'context' => $this->objectManager->getObject(
                    \Magento\Backend\Block\Template\Context::class,
                    ['layout' => $this->layoutMock]
                )
            ]
        );

        return $grid;
    }

    public function testGetItemUnitPriceHtml()
    {
        $html = '$34.28';

        $grid = $this->getGrid();

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_unit_price')
            ->willReturn($this->priceRenderBlock);

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->willReturn($html);

        $this->assertEquals($html, $grid->getItemUnitPriceHtml($this->itemMock));
    }

    public function testGetItemRowTotalHtml()
    {
        $html = '$34.28';

        $grid = $this->getGrid();

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_row_total')
            ->willReturn($this->priceRenderBlock);

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->willReturn($html);

        $this->assertEquals($html, $grid->getItemRowTotalHtml($this->itemMock));
    }

    public function testGetItemRowTotalWithDiscountHtml()
    {
        $html = '$34.28';

        $grid = $this->getGrid();

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_row_total_with_discount')
            ->willReturn($this->priceRenderBlock);

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->willReturn($html);

        $this->assertEquals($html, $grid->getItemRowTotalWithDiscountHtml($this->itemMock));
    }

    /**
     * @param array $orderData
     * @param bool $displayTotalsIncludeTax
     * @param float $expected
     * @dataProvider getSubtotalWithDiscountDataProvider
     */
    public function testGetSubtotalWithDiscount($orderData, $displayTotalsIncludeTax, $expected)
    {
        $quoteAddressMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['getSubtotal', 'getTaxAmount','getDiscountTaxCompensationAmount','getDiscountAmount']
        );
        $gridMock = $this->createPartialMock(
            \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid::class,
            ['getQuoteAddress','displayTotalsIncludeTax']
        );

        $gridMock->expects($this->any())->method('getQuoteAddress')
            ->willReturn($quoteAddressMock);
        $gridMock->expects($this->any())->method('displayTotalsIncludeTax')
            ->willReturn($displayTotalsIncludeTax);

        $quoteAddressMock->expects($this->once())
            ->method('getSubtotal')
            ->willReturn($orderData['subTotal']);

        $quoteAddressMock->expects($this->any())
            ->method('getTaxAmount')
            ->willReturn($orderData['taxAmount']);

        $quoteAddressMock->expects($this->any())
            ->method('getDiscountTaxCompensationAmount')
            ->willReturn($orderData['discountTaxCompensationAmount']);

        $quoteAddressMock->expects($this->once())
            ->method('getDiscountAmount')
            ->willReturn($orderData['discountAmount']);

        $this->assertEquals($expected, $gridMock->getSubtotalWithDiscount());
    }

    /**
     * @return array
     */
    public function getSubtotalWithDiscountDataProvider()
    {
        $result = [];
        $result['displayTotalsIncludeTaxTrue'] = [
            'orderData' => [
                'subTotal' => 32.59,
                'taxAmount' => 8.2,
                'discountTaxCompensationAmount' => 1.72,
                'discountAmount' => -10.24
            ],
            'displayTotalsIncludeTax'=> true,
            'expected' => 32.27
        ];
        $result['displayTotalsIncludeTaxFalse'] = [
            'orderData' => [
                'subTotal' => 66.67,
                'taxAmount' => 20,
                'discountTaxCompensationAmount' => 8,
                'discountAmount' => -34.67
            ],
            'displayTotalsIncludeTax'=> false,
            'expected' => 32
        ];
        return $result;
    }
}

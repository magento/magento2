<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create\Items;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\CatalogInventory\Model\StockState;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Store\Model\Store;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Config;
use Magento\Wishlist\Model\WishlistFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridTest extends TestCase
{
    /**
     * @var MockObject|Grid
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

    /** @var MockObject|Item  */
    protected $itemMock;

    /**
     * @var MockObject|PriceCurrencyInterface
     */
    protected $priceCurrency;

    /** @var MockObject */
    protected $stockItemMock;

    /**
     * @var MockObject
     */
    protected $stockRegistry;

    /**
     * @var MockObject
     */
    protected $stockState;

    /**
     * Initialize required data
     */
    protected function setUp(): void
    {
        $orderCreateMock = $this->createMock(Create::class);
        $taxData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrency = $this->getMockBuilder(
            PriceCurrencyInterface::class
        )->getMock();
        $sessionMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuote'])
            ->getMock();

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore'])
            ->getMock();

        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrency->expects($this->any())
            ->method('convertAndFormat')
            ->willReturnArgument(0);
        $quoteMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $sessionMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $wishlistFactoryMock = $this->getMockBuilder(WishlistFactory::class)
            ->disableOriginalConstructor()
            ->addMethods(['methods'])
            ->getMock();

        $giftMessageSave = $this->getMockBuilder(\Magento\Giftmessage\Model\Save::class)
            ->disableOriginalConstructor()
            ->getMock();

        $taxConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockRegistry = $this->getMockBuilder(StockRegistry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStockItem'])
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getIsInStock']
        );

        $this->stockState = $this->createPartialMock(
            StockState::class,
            ['checkQuoteItemQty']
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->objectManager = new ObjectManager($this);
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);
        $this->block = $this->objectManager->getObject(
            Grid::class,
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

        $this->priceRenderBlock = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->addMethods(['setItem'])
            ->onlyMethods(['toHtml'])
            ->getMock();

        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBlock'])
            ->getMock();

        $this->itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
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
    public static function tierPriceDataProvider()
    {
        return [
            [
                [['price' => 100, 'price_qty' => 1]],
                '1 with 100% discount each',
                Type::TYPE_BUNDLE,
            ],
            [
                [['price' => 100, 'price_qty' => 1], ['price' => 200, 'price_qty' => 2]],
                '1 with 100% discount each<br />2 with 200% discount each',
                Type::TYPE_BUNDLE
            ],
            [
                [['price' => 50, 'price_qty' => 2]],
                '2 for 50',
                Type::TYPE_SIMPLE
            ],
            [
                [['price' => 50, 'price_qty' => 2], ['price' => 150, 'price_qty' => 3]],
                '2 for 50<br />3 for 150',
                Type::TYPE_SIMPLE
            ],
            [0, '', Type::TYPE_SIMPLE]
        ];
    }

    /**
     * @param array|int $tierPrices
     * @param string $productType
     * @return MockObject|Item
     */
    protected function prepareItem($tierPrices, $productType)
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTierPrice', 'getStatus'])
            ->getMock();
        $product->expects($this->once())->method('getTierPrice')->willReturn($tierPrices);
        $item = $this->getMockBuilder(Item::class)
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
        $layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $blockMock = $this->getMockBuilder(AbstractBlock::class)
            ->addMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $itemMock = $this->createPartialMock(
            Item::class,
            ['getProduct', 'setHasError', 'setQty', 'getQty', 'getChildren']
        );
        $productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getStockItem'])
            ->onlyMethods(['getStatus', 'getID'])
            ->disableOriginalConstructor()
            ->getMock();

        $checkMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getMessage', 'getHasError'])
            ->disableOriginalConstructor()
            ->getMock();

        $layoutMock->expects($this->once())->method('getParentName')->willReturn('parentBlock');
        $layoutMock->expects($this->once())->method('getBlock')->with('parentBlock')
            ->willReturn($blockMock);

        $blockMock->expects($this->once())->method('getItems')->willReturn([$itemMock]);

        $itemMock->expects($this->any())->method('getChildren')->willReturn([$itemMock]);
        $itemMock->expects($this->any())->method('getProduct')->willReturn($productMock);
        $itemMock->expects($this->any())->method('getQty')->willReturn($itemQty);

        $productMock->expects($this->any())->method('getId')->willReturn($productId);
        $productMock->expects($this->any())->method('getStatus')
            ->willReturn(Status::STATUS_ENABLED);

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
     * @return Grid
     */
    protected function getGrid()
    {
        /** @var Grid  $grid */
        $grid = $this->objectManager->getObject(
            Grid::class,
            [
                'context' => $this->objectManager->getObject(
                    Context::class,
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
        $quoteAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getSubtotal', 'getTaxAmount', 'getDiscountTaxCompensationAmount', 'getDiscountAmount'])
            ->disableOriginalConstructor()
            ->getMock();
        $gridMock = $this->createPartialMock(
            Grid::class,
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
    public static function getSubtotalWithDiscountDataProvider()
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

<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create\Items;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\CatalogInventory\Model\StockState;
use Magento\Framework\DataObject;
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
        $orderCreateMock = $this->createPartialMock(Create::class, ['__wakeup']);
        $taxData = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->getMock();
        $this->priceCurrency = $this->getMockBuilder(
            PriceCurrencyInterface::class
        )->getMock();
        $sessionMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', '__wakeup'])
            ->getMock();

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup'])
            ->getMock();

        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
        $this->priceCurrency->expects($this->any())
            ->method('convertAndFormat')
            ->will($this->returnArgument(0));
        $quoteMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $sessionMock->expects($this->any())->method('getQuote')->will($this->returnValue($quoteMock));
        $wishlistFactoryMock = $this->getMockBuilder(\Magento\Wishlist\Model\WishlistFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['methods', '__wakeup'])
            ->getMock();

        $giftMessageSave = $this->getMockBuilder(\Magento\Giftmessage\Model\Save::class)
            ->setMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $taxConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();

        $this->stockRegistry = $this->getMockBuilder(StockRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getIsInStock', '__wakeup']
        );

        $this->stockState = $this->createPartialMock(
            StockState::class,
            ['checkQuoteItemQty', '__wakeup']
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $this->objectManager = new ObjectManager($this);
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
            ->setMethods(['setItem', 'toHtml'])
            ->getMock();

        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBlock'])
            ->getMock();

        $this->itemMock = $this->getMockBuilder(Item::class)
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
            ->setMethods(['getTierPrice', '__wakeup', 'getStatus'])
            ->getMock();
        $product->expects($this->once())->method('getTierPrice')->will($this->returnValue($tierPrices));
        $item = $this->getMockBuilder(Item::class)
            ->setConstructorArgs(['getProduct', 'getProductType'])
            ->disableOriginalConstructor()
            ->getMock();
        $item->expects($this->once())->method('getProduct')->will($this->returnValue($product));

        $calledTimes = $tierPrices ? 'once' : 'never';
        $item->expects($this->{$calledTimes}())->method('getProductType')->will($this->returnValue($productType));
        return $item;
    }

    /**
     * @covers \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid::getItems
     */
    public function testGetItems()
    {
        $productId = 8;
        $itemQty = 23;
        $layoutMock = $this->createMock(LayoutInterface::class);
        $blockMock = $this->createPartialMock(AbstractBlock::class, ['getItems']);

        $itemMock = $this->createPartialMock(
            Item::class,
            ['getProduct', 'setHasError', 'setQty', 'getQty', '__sleep', '__wakeup', 'getChildren']
        );
        $productMock = $this->createPartialMock(
            Product::class,
            ['getStockItem', 'getID', '__sleep', '__wakeup', 'getStatus']
        );

        $checkMock = $this->createPartialMock(DataObject::class, ['getMessage', 'getHasError']);

        $layoutMock->expects($this->once())->method('getParentName')->will($this->returnValue('parentBlock'));
        $layoutMock->expects($this->once())->method('getBlock')->with('parentBlock')
            ->will($this->returnValue($blockMock));

        $blockMock->expects($this->once())->method('getItems')->will($this->returnValue([$itemMock]));

        $itemMock->expects($this->any())->method('getChildren')->will($this->returnValue([$itemMock]));
        $itemMock->expects($this->any())->method('getProduct')->will($this->returnValue($productMock));
        $itemMock->expects($this->any())->method('getQty')->will($this->returnValue($itemQty));

        $productMock->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $productMock->expects($this->any())->method('getStatus')
            ->will($this->returnValue(Status::STATUS_ENABLED));

        $checkMock->expects($this->any())->method('getMessage')->will($this->returnValue('Message'));
        $checkMock->expects($this->any())->method('getHasError')->will($this->returnValue(false));

        $this->stockState->expects($this->once())
            ->method('checkQuoteItemQty')
            ->with(
                $productId,
                $itemQty,
                $itemQty,
                $itemQty,
                null
            )
            ->will($this->returnValue($checkMock));

        $this->block->getQuote()->setIsSuperMode(true);
        $items = $this->block->setLayout($layoutMock)->getItems();

        $this->assertEquals('Message', $items[0]->getMessage());
        $this->assertEquals(true, $this->block->getQuote()->getIsSuperMode());
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
            ->will($this->returnValue($this->priceRenderBlock));

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($html));

        $this->assertEquals($html, $grid->getItemUnitPriceHtml($this->itemMock));
    }

    public function testGetItemRowTotalHtml()
    {
        $html = '$34.28';

        $grid = $this->getGrid();

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_row_total')
            ->will($this->returnValue($this->priceRenderBlock));

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($html));

        $this->assertEquals($html, $grid->getItemRowTotalHtml($this->itemMock));
    }

    public function testGetItemRowTotalWithDiscountHtml()
    {
        $html = '$34.28';

        $grid = $this->getGrid();

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('item_row_total_with_discount')
            ->will($this->returnValue($this->priceRenderBlock));

        $this->priceRenderBlock->expects($this->once())
            ->method('setItem')
            ->with($this->itemMock);

        $this->priceRenderBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($html));

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
            Address::class,
            ['getSubtotal', 'getTaxAmount','getDiscountTaxCompensationAmount','getDiscountAmount']
        );
        $gridMock = $this->createPartialMock(
            Grid::class,
            ['getQuoteAddress','displayTotalsIncludeTax']
        );

        $gridMock->expects($this->any())->method('getQuoteAddress')
            ->will($this->returnValue($quoteAddressMock));
        $gridMock->expects($this->any())->method('displayTotalsIncludeTax')
            ->will($this->returnValue($displayTotalsIncludeTax));

        $quoteAddressMock->expects($this->once())
            ->method('getSubtotal')
            ->will($this->returnValue($orderData['subTotal']));

        $quoteAddressMock->expects($this->any())
            ->method('getTaxAmount')
            ->will($this->returnValue($orderData['taxAmount']));

        $quoteAddressMock->expects($this->any())
            ->method('getDiscountTaxCompensationAmount')
            ->will($this->returnValue($orderData['discountTaxCompensationAmount']));

        $quoteAddressMock->expects($this->once())
            ->method('getDiscountAmount')
            ->will($this->returnValue($orderData['discountAmount']));

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

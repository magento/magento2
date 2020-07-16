<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\CustomerData;

use Magento\Catalog\Block\ShortcutButtons;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\CustomerData\ItemPoolInterface;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Store\Model\System\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTest extends TestCase
{
    /**
     * @var Cart
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var MockObject
     */
    protected $catalogUrlMock;

    /**
     * @var MockObject
     */
    protected $checkoutCartMock;

    /**
     * @var MockObject
     */
    protected $checkoutHelperMock;

    /**
     * @var MockObject
     */
    protected $itemPoolInterfaceMock;

    /**
     * @var MockObject
     */
    protected $layoutMock;

    protected function setUp(): void
    {
        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->catalogUrlMock = $this->createPartialMock(
            Url::class,
            ['getRewriteByProductStore']
        );
        $this->checkoutCartMock = $this->createMock(\Magento\Checkout\Model\Cart::class);
        $this->checkoutHelperMock = $this->createMock(Data::class);
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->itemPoolInterfaceMock = $this->getMockForAbstractClass(ItemPoolInterface::class);

        $this->model = new Cart(
            $this->checkoutSessionMock,
            $this->catalogUrlMock,
            $this->checkoutCartMock,
            $this->checkoutHelperMock,
            $this->itemPoolInterfaceMock,
            $this->layoutMock
        );
    }

    public function testIsGuestCheckoutAllowed()
    {
        $quoteMock = $this->createMock(Quote::class);
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->checkoutHelperMock->expects($this->once())->method('isAllowedGuestCheckout')->with($quoteMock)
            ->willReturn(true);

        $this->assertTrue($this->model->isGuestCheckoutAllowed());
    }

    public function testGetSectionData()
    {
        $summaryQty = 100;
        $subtotalValue = 200;
        $productId = 10;
        $storeId = 20;
        $productRewrite = [$productId => ['rewrite' => 'product']];
        $itemData = ['item' => 'data'];
        $shortcutButtonsHtml = '<span>Buttons</span>';
        $websiteId = 100;

        $subtotalMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $subtotalMock->expects($this->once())->method('getValue')->willReturn($subtotalValue);
        $totals = ['subtotal' => $subtotalMock];

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getHasError'])
            ->onlyMethods(['getTotals', 'getAllVisibleItems', 'getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock->expects($this->exactly(2))->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getTotals')->willReturn($totals);
        $quoteMock->expects($this->once())->method('getHasError')->willReturn(false);

        $this->checkoutCartMock->expects($this->once())->method('getSummaryQty')->willReturn($summaryQty);
        $this->checkoutHelperMock->expects($this->once())
            ->method('formatPrice')
            ->with($subtotalValue)
            ->willReturn($subtotalValue);
        $this->checkoutHelperMock->expects($this->once())->method('canOnepageCheckout')->willReturn(true);

        $quoteItemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getStoreId'])
            ->onlyMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn([$quoteItemMock]);

        $storeMock = $this->getMockBuilder(Store::class)
            ->addMethods(['getWebsiteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $quoteMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['setUrlDataObject'])
            ->onlyMethods(['isVisibleInSiteVisibility', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteItemMock->expects($this->exactly(3))->method('getProduct')->willReturn($productMock);
        $quoteItemMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $productMock->expects($this->once())->method('isVisibleInSiteVisibility')->willReturn(false);
        $productMock->expects($this->exactly(3))->method('getId')->willReturn($productId);
        $productMock->expects($this->once())
            ->method('setUrlDataObject')
            ->with(new DataObject($productRewrite[$productId]))
            ->willReturnSelf();

        $this->catalogUrlMock->expects($this->once())
            ->method('getRewriteByProductStore')
            ->with([$productId => $storeId])
            ->willReturn($productRewrite);

        $this->itemPoolInterfaceMock->expects($this->once())
            ->method('getItemData')
            ->with($quoteItemMock)
            ->willReturn($itemData);

        $shortcutButtonsMock = $this->createMock(ShortcutButtons::class);
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(ShortcutButtons::class)
            ->willReturn($shortcutButtonsMock);

        $shortcutButtonsMock->expects($this->once())->method('toHtml')->willReturn($shortcutButtonsHtml);
        $this->checkoutHelperMock->expects($this->once())
            ->method('isAllowedGuestCheckout')
            ->with($quoteMock)
            ->willReturn(true);

        $expectedResult = [
            'summary_count' => 100,
            'subtotal' => 200,
            'possible_onepage_checkout' => 1,
            'items' => [
                ['item' => 'data']
            ],
            'extra_actions' => '<span>Buttons</span>',
            'isGuestCheckoutAllowed' => 1,
            'website_id' => $websiteId,
            'subtotalAmount' => 200,
            'storeId' => null
        ];
        $this->assertEquals($expectedResult, $this->model->getSectionData());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetSectionDataWithCompositeProduct()
    {
        $summaryQty = 100;
        $subtotalValue = 200;
        $productId = 10;
        $storeId = 20;
        $websiteId = 100;

        $productRewrite = [$productId => ['rewrite' => 'product']];
        $itemData = ['item' => 'data'];
        $shortcutButtonsHtml = '<span>Buttons</span>';
        $subtotalMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $subtotalMock->expects($this->once())->method('getValue')->willReturn($subtotalValue);
        $totals = ['subtotal' => $subtotalMock];

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getHasError'])
            ->onlyMethods(['getTotals', 'getAllVisibleItems', 'getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteItemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getStoreId'])
            ->onlyMethods(['getProduct', 'getOptionByCode'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock->expects($this->exactly(2))->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getTotals')->willReturn($totals);
        $quoteMock->expects($this->once())->method('getHasError')->willReturn(false);

        $storeMock = $this->getMockBuilder(Store::class)
            ->addMethods(['getWebsiteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $quoteMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $this->checkoutCartMock->expects($this->once())->method('getSummaryQty')->willReturn($summaryQty);
        $this->checkoutHelperMock->expects($this->once())
            ->method('formatPrice')
            ->with($subtotalValue)
            ->willReturn($subtotalValue);
        $this->checkoutHelperMock->expects($this->once())->method('canOnepageCheckout')->willReturn(true);

        $quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn([$quoteItemMock]);

        $productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['setUrlDataObject'])
            ->onlyMethods(['isVisibleInSiteVisibility', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $optionsMock = $this->createMock(Option::class);
        $optionsMock->expects($this->once())->method('getProduct')->willReturn($productMock);

        $quoteItemMock->expects($this->exactly(2))->method('getProduct')->willReturn($productMock);
        $quoteItemMock->expects($this->exactly(2))
            ->method('getOptionByCode')
            ->with('product_type')
            ->willReturn($optionsMock);
        $quoteItemMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $productMock->expects($this->once())->method('isVisibleInSiteVisibility')->willReturn(false);
        $productMock->expects($this->exactly(3))->method('getId')->willReturn($productId);
        $productMock->expects($this->once())
            ->method('setUrlDataObject')
            ->with(new DataObject($productRewrite[$productId]))
            ->willReturnSelf();

        $this->catalogUrlMock->expects($this->once())
            ->method('getRewriteByProductStore')
            ->with([$productId => $storeId])
            ->willReturn($productRewrite);

        $shortcutButtonsMock = $this->createMock(ShortcutButtons::class);
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(ShortcutButtons::class)
            ->willReturn($shortcutButtonsMock);

        $shortcutButtonsMock->expects($this->once())->method('toHtml')->willReturn($shortcutButtonsHtml);
        $this->checkoutHelperMock->expects($this->once())
            ->method('isAllowedGuestCheckout')
            ->with($quoteMock)
            ->willReturn(true);

        $this->itemPoolInterfaceMock->expects($this->once())
            ->method('getItemData')
            ->with($quoteItemMock)
            ->willReturn($itemData);

        $expectedResult = [
            'summary_count' => 100,
            'subtotal' => 200,
            'possible_onepage_checkout' => 1,
            'items' => [
                ['item' => 'data']
            ],
            'extra_actions' => '<span>Buttons</span>',
            'isGuestCheckoutAllowed' => 1,
            'website_id' => $websiteId,
            'subtotalAmount' => 200,
            'storeId' => null
        ];
        $this->assertEquals($expectedResult, $this->model->getSectionData());
    }
}

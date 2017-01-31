<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Unit\CustomerData;

class CartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\CustomerData\Cart
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogUrlMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutCartMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemPoolInterfaceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    protected function setUp()
    {
        $this->checkoutSessionMock = $this->getMock('\Magento\Checkout\Model\Session', [], [], '', false);
        $this->catalogUrlMock = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Url',
            ['getRewriteByProductStore'],
            [],
            '',
            false
        );
        $this->checkoutCartMock = $this->getMock('\Magento\Checkout\Model\Cart', [], [], '', false);
        $this->checkoutHelperMock = $this->getMock('\Magento\Checkout\Helper\Data', [], [], '', false);
        $this->layoutMock = $this->getMock('\Magento\Framework\View\LayoutInterface', [], [], '', false);
        $this->itemPoolInterfaceMock = $this->getMock(
            '\Magento\Checkout\CustomerData\ItemPoolInterface',
            [],
            [],
            '',
            false
        );

        $this->model = new \Magento\Checkout\CustomerData\Cart(
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
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
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
        $websiteId = 100;
        $productRewrite = [$productId => ['rewrite' => 'product']];
        $itemData = ['item' => 'data'];
        $shortcutButtonsHtml = '<span>Buttons</span>';

        $subtotalMock = $this->getMock('\Magento\Framework\DataObject', ['getValue'], [], '', false);
        $subtotalMock->expects($this->once())->method('getValue')->willReturn($subtotalValue);
        $totals = ['subtotal' => $subtotalMock];

        $quoteMock = $this->getMock(
            '\Magento\Quote\Model\Quote',
            ['getTotals', 'getHasError', 'getAllVisibleItems', 'getStore'],
            [],
            '',
            false
        );
        $this->checkoutSessionMock->expects($this->exactly(2))->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getTotals')->willReturn($totals);
        $quoteMock->expects($this->once())->method('getHasError')->willReturn(false);

        $storeMock = $this->getMock(\Magento\Store\Model\System\Store::class, ['getWebsiteId'], [], '', false);
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $quoteMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $this->checkoutCartMock->expects($this->once())->method('getSummaryQty')->willReturn($summaryQty);
        $this->checkoutHelperMock->expects($this->once())
            ->method('formatPrice')
            ->with($subtotalValue)
            ->willReturn($subtotalValue);
        $this->checkoutHelperMock->expects($this->once())->method('canOnepageCheckout')->willReturn(true);

        $quoteItemMock = $this->getMock('\Magento\Quote\Model\Quote\Item', ['getProduct', 'getStoreId'], [], '', false);
        $quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn([$quoteItemMock]);

        $productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['isVisibleInSiteVisibility', 'getId', 'setUrlDataObject'],
            [],
            '',
            false
        );
        $quoteItemMock->expects($this->exactly(3))->method('getProduct')->willReturn($productMock);
        $quoteItemMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $productMock->expects($this->once())->method('isVisibleInSiteVisibility')->willReturn(false);
        $productMock->expects($this->exactly(3))->method('getId')->willReturn($productId);
        $productMock->expects($this->once())
            ->method('setUrlDataObject')
            ->with(new \Magento\Framework\DataObject($productRewrite[$productId]))
            ->willReturnSelf();

        $this->catalogUrlMock->expects($this->once())
            ->method('getRewriteByProductStore')
            ->with([$productId => $storeId])
            ->willReturn($productRewrite);

        $this->itemPoolInterfaceMock->expects($this->once())
            ->method('getItemData')
            ->with($quoteItemMock)
            ->willReturn($itemData);

        $shortcutButtonsMock = $this->getMock('\Magento\Catalog\Block\ShortcutButtons', [], [], '', false);
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with('Magento\Catalog\Block\ShortcutButtons')
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
            'website_id' => $websiteId
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
        $subtotalMock = $this->getMock('\Magento\Framework\DataObject', ['getValue'], [], '', false);
        $subtotalMock->expects($this->once())->method('getValue')->willReturn($subtotalValue);
        $totals = ['subtotal' => $subtotalMock];

        $quoteMock = $this->getMock(
            '\Magento\Quote\Model\Quote',
            ['getTotals', 'getHasError', 'getAllVisibleItems', 'getStore'],
            [],
            '',
            false
        );
        $quoteItemMock = $this->getMock(
            '\Magento\Quote\Model\Quote\Item',
            ['getProduct', 'getOptionByCode', 'getStoreId'],
            [],
            '',
            false
        );

        $this->checkoutSessionMock->expects($this->exactly(2))->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getTotals')->willReturn($totals);
        $quoteMock->expects($this->once())->method('getHasError')->willReturn(false);

        $this->checkoutCartMock->expects($this->once())->method('getSummaryQty')->willReturn($summaryQty);
        $this->checkoutHelperMock->expects($this->once())
            ->method('formatPrice')
            ->with($subtotalValue)
            ->willReturn($subtotalValue);
        $this->checkoutHelperMock->expects($this->once())->method('canOnepageCheckout')->willReturn(true);

        $quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn([$quoteItemMock]);

        $storeMock = $this->getMock(\Magento\Store\Model\System\Store::class, ['getWebsiteId'], [], '', false);
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $quoteMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['isVisibleInSiteVisibility', 'getId', 'setUrlDataObject'],
            [],
            '',
            false
        );

        $optionsMock = $this->getMock('\Magento\Quote\Model\Quote\Item\Option', [], [], '', false);
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
            ->with(new \Magento\Framework\DataObject($productRewrite[$productId]))
            ->willReturnSelf();

        $this->catalogUrlMock->expects($this->once())
            ->method('getRewriteByProductStore')
            ->with([$productId => $storeId])
            ->willReturn($productRewrite);

        $shortcutButtonsMock = $this->getMock('\Magento\Catalog\Block\ShortcutButtons', [], [], '', false);
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with('Magento\Catalog\Block\ShortcutButtons')
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
            'website_id' => $websiteId
        ];
        $this->assertEquals($expectedResult, $this->model->getSectionData());
    }
}

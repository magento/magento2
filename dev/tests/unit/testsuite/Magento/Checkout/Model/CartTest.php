<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CartTest
 */
class CartTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Checkout\Model\Cart */
    protected $cart;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /** @var \Magento\CatalogInventory\Api\StockItem|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockState;

    protected function setUp()
    {
        $this->checkoutSessionMock = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);
        $this->customerSessionMock = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->stockRegistry = $this->getMockBuilder('Magento\CatalogInventory\Model\StockRegistry')
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->getMock(
            'Magento\CatalogInventory\Model\Stock\Item',
            ['getMinSaleQty', '__wakeup'],
            [],
            '',
            false
        );

        $this->stockState = $this->getMock(
            'Magento\CatalogInventory\Model\StockState',
            ['suggestQty', '__wakeup'],
            [],
            '',
            false
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));
        $this->quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->cart = $this->objectManagerHelper->getObject(
            'Magento\Checkout\Model\Cart',
            [
                'scopeConfig' => $this->scopeConfigMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'stockRegistry' => $this->stockRegistry,
                'stockState' => $this->stockState,
                'customerSession' => $this->customerSessionMock,
                'eventManager' => $this->eventManagerMock
            ]
        );
    }

    public function testSuggestItemsQty()
    {
        $data = [[] , ['qty' => -2], ['qty' => 3], ['qty' => 3.5], ['qty' => 5], ['qty' => 4]];
        $this->quoteMock->expects($this->any())
            ->method('getItemById')
            ->will($this->returnValueMap([
                [2, $this->prepareQuoteItemMock(2)],
                [3, $this->prepareQuoteItemMock(3)],
                [4, $this->prepareQuoteItemMock(4)],
                [5, $this->prepareQuoteItemMock(5)],
            ]));

        $this->stockState->expects($this->at(0))
            ->method('suggestQty')
            ->will($this->returnValue(3.0));
        $this->stockState->expects($this->at(1))
            ->method('suggestQty')
            ->will($this->returnValue(3.5));

        $this->checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));

        $this->assertSame(
            [
                [],
                ['qty' => -2],
                ['qty' => 3., 'before_suggest_qty' => 3.],
                ['qty' => 3.5, 'before_suggest_qty' => 3.5],
                ['qty' => 5],
                ['qty' => 4],
            ],
            $this->cart->suggestItemsQty($data)
        );
    }

    public function testUpdateItems()
    {
        $data = [['qty' => 5.5, 'before_suggest_qty' => 5.5]];
        $infoDataObject = $this->objectManagerHelper->getObject('Magento\Framework\Object', ['data' => $data]);

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));
        $this->eventManagerMock->expects($this->at(0))->method('dispatch')->with(
            'checkout_cart_update_items_before',
            ['cart' => $this->cart, 'info' => $infoDataObject]
        );
        $this->eventManagerMock->expects($this->at(1))->method('dispatch')->with(
            'checkout_cart_update_items_after',
            ['cart' => $this->cart, 'info' => $infoDataObject]
        );

        $result = $this->cart->updateItems($data);
        $this->assertSame($this->cart, $result);
    }

    /**
     * @param int|bool $itemId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function prepareQuoteItemMock($itemId)
    {
        $store = $this->getMock('Magento\Store\Model\Store', ['getWebsiteId', '__wakeup'], [], '', false);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(10));
        switch ($itemId) {
            case 2:
                $product = $this->getMock(
                    'Magento\Catalog\Model\Product',
                    ['getStore', 'getId', '__wakeup'],
                    [],
                    '',
                    false
                );
                $product->expects($this->once())
                    ->method('getId')
                    ->will($this->returnValue(4));
                $product->expects($this->once())
                    ->method('getStore')
                    ->will($this->returnValue($store));
                break;
            case 3:
                $product = $this->getMock(
                    'Magento\Catalog\Model\Product',
                    ['getStore', 'getId', '__wakeup'],
                    [],
                    '',
                    false
                );
                $product->expects($this->once())
                    ->method('getId')
                    ->will($this->returnValue(5));
                $product->expects($this->once())
                    ->method('getStore')
                    ->will($this->returnValue($store));
                break;
            case 4:
                $product = false;
                break;
            default:
                return false;
        }

        $quoteItem = $this->getMock('Magento\Sales\Model\Quote\Item', [], [], '', false);
        $quoteItem->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($product));
        return $quoteItem;
    }

    /**
     * @param boolean $useQty
     * @dataProvider useQtyDataProvider
     */
    public function testGetSummaryQty($useQty)
    {
        $quoteId = 1;
        $itemsCount = 1;
        $quoteMock = $this->getMock(
            'Magento\Sales\Model\Quote',
            ['getItemsCount', 'getItemsQty', '__wakeup'],
            [],
            '',
            false
        );

        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->checkoutSessionMock->expects($this->at(2))->method('getQuoteId')->will($this->returnValue($quoteId));
        $this->customerSessionMock->expects($this->any())->method('isLoggedIn')->will($this->returnValue(true));

        $this->scopeConfigMock->expects($this->once())->method('getValue')
            ->with('checkout/cart_link/use_qty', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue($useQty));

        $qtyMethodName = ($useQty) ? 'getItemsQty' : 'getItemsCount';
        $quoteMock->expects($this->once())->method($qtyMethodName)->will($this->returnValue($itemsCount));

        $this->assertEquals($itemsCount, $this->cart->getSummaryQty());
    }

    public function useQtyDataProvider()
    {
        return [
            ['useQty' => true],
            ['useQty' => false]
        ];
    }
}

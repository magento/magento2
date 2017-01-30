<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Observer;

use \Magento\Wishlist\Observer\CartUpdateBefore as Observer;

class CartUpdateBeforeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Observer
     */
    protected $observer;

    /**
     * @var \Magento\Wishlist\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistFactory;

    /**
     * @var \Magento\Wishlist\Model\Wishlist|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlist;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    protected function setUp()
    {
        $this->helper = $this->getMockBuilder('Magento\Wishlist\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->wishlistFactory = $this->getMockBuilder('Magento\Wishlist\Model\WishlistFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->wishlist = $this->getMockBuilder('Magento\Wishlist\Model\Wishlist')
            ->disableOriginalConstructor()
            ->getMock();
        $this->wishlistFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->wishlist);

        $this->observer = new Observer(
            $this->helper,
            $this->wishlistFactory
        );
    }

    /**
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $customerId = 1;
        $itemId = 5;
        $itemQty = 123;
        $productId = 321;

        $eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();

        $event = $this->getMockBuilder('Magento\Framework\Event')
            ->setMethods(['getCart', 'getInfo'])
            ->disableOriginalConstructor()
            ->getMock();

        $eventObserver->expects($this->exactly(2))
            ->method('getEvent')
            ->willReturn($event);

        $quoteItem = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->setMethods(['getProductId', 'getBuyRequest', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $buyRequest = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(['setQty'])
            ->disableOriginalConstructor()
            ->getMock();

        $infoData = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(['toArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $infoData->expects($this->once())
            ->method('toArray')
            ->willReturn([$itemId => ['qty' => $itemQty, 'wishlist' => true]]);

        $cart = $this->getMockBuilder('Magento\Checkout\Model\Cart')->disableOriginalConstructor()->getMock();
        $quote = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->setMethods(['getCustomerId', 'getItemById', 'removeItem', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getCart')
            ->willReturn($cart);

        $event->expects($this->once())
            ->method('getInfo')
            ->willReturn($infoData);

        $cart->expects($this->any())
            ->method('getQuote')
            ->willReturn($quote);

        $quoteItem->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);
        $quoteItem->expects($this->once())
            ->method('getBuyRequest')
            ->willReturn($buyRequest);

        $buyRequest->expects($this->once())
            ->method('setQty')
            ->with($itemQty)
            ->willReturnSelf();

        $quote->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $quote->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->willReturn($quoteItem);

        $quote->expects($this->once())
            ->method('removeItem')
            ->with($itemId);

        $this->wishlist->expects($this->once())
            ->method('loadByCustomerId')
            ->with($this->logicalOr($customerId, true))
            ->willReturnSelf();

        $this->wishlist->expects($this->once())
            ->method('addNewItem')
            ->with($this->logicalOr($productId, $buyRequest));

        $this->wishlist->expects($this->once())
            ->method('save');

        $this->helper->expects($this->once())
            ->method('calculate');

        /** @var $eventObserver \Magento\Framework\Event\Observer */
        $this->assertSame(
            $this->observer,
            $this->observer->execute($eventObserver)
        );
    }
}

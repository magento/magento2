<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Wishlist\Test\Unit\Observer;

use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Observer\CartUpdateBefore as Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartUpdateBeforeTest extends TestCase
{
    /**
     * @var Observer
     */
    protected $observer;

    /**
     * @var Data|MockObject
     */
    protected $helper;

    /**
     * @var Session|MockObject
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session|MockObject
     */
    protected $customerSession;

    /**
     * @var WishlistFactory|MockObject
     */
    protected $wishlistFactory;

    /**
     * @var Wishlist|MockObject
     */
    protected $wishlist;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManager;

    protected function setUp(): void
    {
        $this->helper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wishlistFactory = $this->getMockBuilder(WishlistFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->wishlist = $this->getMockBuilder(Wishlist::class)
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

        $eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = $this->getMockBuilder(Event::class)
            ->setMethods(['getCart', 'getInfo'])
            ->disableOriginalConstructor()
            ->getMock();

        $eventObserver->expects($this->exactly(2))
            ->method('getEvent')
            ->willReturn($event);

        $quoteItem = $this->getMockBuilder(Item::class)
            ->setMethods(['getProductId', 'getBuyRequest', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $buyRequest = $this->getMockBuilder(DataObject::class)
            ->setMethods(['setQty'])
            ->disableOriginalConstructor()
            ->getMock();

        $infoData = $this->getMockBuilder(DataObject::class)
            ->setMethods(['toArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $infoData->expects($this->once())
            ->method('toArray')
            ->willReturn([$itemId => ['qty' => $itemQty, 'wishlist' => true]]);

        $cart = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->getMockBuilder(Quote::class)
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

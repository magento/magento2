<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->helper = $this->getMockBuilder('Magento\Wishlist\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSession = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->setMethods(
                [
                    'getSharedWishlist',
                    'getWishlistPendingMessages',
                    'getWishlistPendingUrls',
                    'getWishlistIds',
                    'getSingleWishlistId',
                    'setSingleWishlistId',
                    'setWishlistIds',
                    'setWishlistPendingUrls',
                    'setWishlistPendingMessages',
                    'setNoCartRedirect',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSession = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(['setWishlistItemCount', 'isLoggedIn', 'getCustomerId'])
            ->getMock();
        $this->wishlistFactory = $this->getMockBuilder('Magento\Wishlist\Model\WishlistFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->wishlist = $this->getMockBuilder('Magento\Wishlist\Model\Wishlist')
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManager = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->getMock();

        $this->wishlistFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->wishlist);

        $this->observer = new Observer(
            $this->helper,
            $this->checkoutSession,
            $this->customerSession,
            $this->wishlistFactory,
            $this->messageManager
        );
    }

    public function testCustomerLogin()
    {
        $event = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var $event \Magento\Framework\Event\Observer */

        $this->helper->expects($this->once())
            ->method('calculate');

        $this->assertInstanceOf(
            'Magento\Wishlist\Model\Observer',
            $this->observer->customerLogin($event)
        );
    }

    public function testCustomerLogout()
    {
        $event = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var $event \Magento\Framework\Event\Observer */

        $this->customerSession->expects($this->once())
            ->method('setWishlistItemCount')
            ->with($this->equalTo(0));

        $this->assertInstanceOf(
            'Magento\Wishlist\Model\Observer',
            $this->observer->customerLogout($event)
        );
    }

    /**
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProcessCartUpdateBefore()
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

        $quoteItem = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->setMethods(['getProductId', 'getBuyRequest', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $buyRequest = $this->getMockBuilder('Magento\Framework\Object')
            ->setMethods(['setQty'])
            ->disableOriginalConstructor()
            ->getMock();

        $infoData = $this->getMockBuilder('Magento\Framework\Object')
            ->setMethods(['toArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $infoData->expects($this->once())
            ->method('toArray')
            ->willReturn([$itemId => ['qty' => $itemQty, 'wishlist' => true]]);

        $cart = $this->getMockBuilder('Magento\Checkout\Model\Cart')->disableOriginalConstructor()->getMock();
        $quote = $this->getMockBuilder('Magento\Sales\Model\Quote')
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
            $this->observer->processCartUpdateBefore($eventObserver)
        );
    }

    public function testProcessAddToCart()
    {
        $wishlistId = 1;
        $customerId = 2;
        $url = 'http://some.pending/url';
        $message = 'some error msg';

        $eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder('Magento\Framework\Event')
            ->setMethods(['getRequest', 'getResponse'])
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMockBuilder('Magento\Framework\App\RequestInterface')->getMock();
        $response = $this->getMockBuilder('Magento\Framework\App\ResponseInterface')
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();
        $wishlists = $this->getMockBuilder('Magento\Wishlist\Model\Resource\Wishlist\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $loadedWishlist = $this->getMockBuilder('Magento\Wishlist\Model\Wishlist\Item')
            ->setMethods(['getId', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $eventObserver->expects($this->any())->method('getEvent')->willReturn($event);

        $request->expects($this->any())->method('getParam')->with('wishlist_next')->willReturn(true);
        $event->expects($this->once())->method('getRequest')->willReturn($request);

        $this->checkoutSession->expects($this->once())->method('getSharedWishlist');
        $this->checkoutSession->expects($this->once())->method('getWishlistPendingMessages')->willReturn([$message]);
        $this->checkoutSession->expects($this->once())->method('getWishlistPendingUrls')->willReturn([$url]);
        $this->checkoutSession->expects($this->once())->method('getWishlistIds');
        $this->checkoutSession->expects($this->once())->method('getSingleWishlistId')->willReturn($wishlistId);

        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->wishlist->expects($this->once())
            ->method('loadByCustomerId')
            ->with($this->logicalOr($customerId, true))
            ->willReturnSelf();
        $this->wishlist->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($wishlists);
        $loadedWishlist->expects($this->once())
            ->method('getId')
            ->willReturn($wishlistId);
        $loadedWishlist->expects($this->once())
            ->method('delete');
        $wishlists->expects($this->once())
            ->method('load')
            ->willReturn([$loadedWishlist]);
        $this->checkoutSession->expects($this->once())
            ->method('setWishlistIds')
            ->with([])
            ->willReturnSelf();
        $this->checkoutSession->expects($this->once())
            ->method('setSingleWishlistId')
            ->with(null)
            ->willReturnSelf();
        $this->checkoutSession->expects($this->once())
            ->method('setWishlistPendingUrls')
            ->with([])
            ->willReturnSelf();
        $this->checkoutSession->expects($this->once())
            ->method('setWishlistPendingMessages')
            ->with([])
            ->willReturnSelf();
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with($message)
            ->willReturnSelf();
        $event->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);
        $response->expects($this->once())
            ->method('setRedirect')
            ->with($url);
        $this->checkoutSession->expects($this->once())
            ->method('setNoCartRedirect')
            ->with(true);

        /** @var $eventObserver \Magento\Framework\Event\Observer */
        $this->observer->processAddToCart($eventObserver);
    }
}

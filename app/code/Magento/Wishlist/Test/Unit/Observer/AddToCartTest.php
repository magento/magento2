<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Observer;

use \Magento\Wishlist\Observer\AddToCart as Observer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddToCartTest extends \PHPUnit\Framework\TestCase
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
        $this->checkoutSession = $this->getMockBuilder(
            \Magento\Checkout\Model\Session::class
        )->setMethods(
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
        )->disableOriginalConstructor()->getMock();
        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['setWishlistItemCount', 'isLoggedIn', 'getCustomerId'])
            ->getMock();
        $this->wishlistFactory = $this->getMockBuilder(\Magento\Wishlist\Model\WishlistFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->wishlist = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMock();

        $this->wishlistFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->wishlist);

        $this->observer = new Observer(
            $this->checkoutSession,
            $this->customerSession,
            $this->wishlistFactory,
            $this->messageManager
        );
    }

    public function testExecute()
    {
        $wishlistId = 1;
        $customerId = 2;
        $url = 'http://some.pending/url';
        $message = 'some error msg';

        $eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->setMethods(['getRequest', 'getResponse'])
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMock();
        $response = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();
        $wishlists = $this->getMockBuilder(\Magento\Wishlist\Model\ResourceModel\Wishlist\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loadedWishlist = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist\Item::class)
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
        $this->observer->execute($eventObserver);
    }
}
